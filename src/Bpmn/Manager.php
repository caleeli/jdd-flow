<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Models\Process;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use JDD\Workflow\Jobs\ScriptTaskJob;
use ProcessMaker\Nayra\Storage\BpmnElement;

class Manager
{
    /**
     * @var \ProcessMaker\Nayra\Contracts\RepositoryInterface $repository
     */
    private $repository;

    /**
     * @var \ProcessMaker\Nayra\Contracts\EventBusInterface $dispatcher
     */
    private $dispatcher;

    /**
     * @var TestEngine $engine
     */
    private $engine;

    /**
     *
     * @var BpmnDocument $bpmnRepository
     */
    private $bpmnRepository;

    /**
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     */
    private $process;

    /**
     * @var array $storage
     */
    private $storage = [];

    /**
     * @var string $bpmn
     */
    private $bpmn;

    /**
     * @var ExecutionInstanceInterface $instance
     */
    private $instance;

    /**
     * @var Process $processData
     */
    private $processData;

    /**
     * @var \JDD\Workflow\JDD\Module $module
     */
    private $module;

    public function __construct()
    {
        //$this->prepare();

        //Se podria mover al app service provider
        //$this->listenSaveEvents();
    }

    private function prepare()
    {
        $this->repository = new Repository;
        $this->dispatcher = app('events');
        $this->engine = new TestEngine($this->repository, $this->dispatcher);

        //Load a BpmnFile Repository
        $this->bpmnRepository = new BpmnDocument();
        $this->bpmnRepository->setEngine($this->engine);
        $this->bpmnRepository->setFactory($this->repository);
        $mapping = $this->bpmnRepository->getBpmnElementsMapping();
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'userTask',
            $mapping[BpmnDocument::BPMN_MODEL]['task']
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'association',
            BpmnDocument::SKIP_ELEMENT
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'textAnnotation',
            BpmnDocument::SKIP_ELEMENT
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'documentation',
            BpmnDocument::SKIP_ELEMENT
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'humanPerformer',
            BpmnDocument::SKIP_ELEMENT
        );
        $this->engine->setRepository($this->repository);
        $this->engine->setStorage($this->bpmnRepository);

        $this->listenSaveEvents();
    }

    /**
     * Call a process
     *
     * @param string $processURL
     * @param array $data
     *
     * @return ExecutionInstanceInterface
     */
    public function callProcess($processURL, $data = [])
    {
        $this->prepare();
        //Process
        $process = $this->loadProcess($processURL);
        $instance = $process->call();
        $this->engine->runToNextState();
        $this->saveState();
        return $instance;
    }

    /**
     * Start a process by start event
     *
     * @param strin $processURL
     * @param string $eventId
     * @param array $data
     * @return ExecutionInstanceInterface
     */
    public function startProcess($processURL, $eventId, $data = [])
    {
        $this->prepare();
        //Process
        $process = $this->loadProcess($processURL);
        $event = $this->bpmnRepository->getStartEvent($eventId);

        //Create a new data store
        $dataStorage = $process->getRepository()->createDataStore();
        $dataStorage->setData($data);
        $instance = $process->getEngine()->createExecutionInstance(
            $process,
            $dataStorage
        );
        $event->start($instance);

        $this->engine->runToNextState();
        $this->saveState();
        return $instance;
    }

    /**
     * Obtiene la lista de tareas del proceso para el usuario actual.
     *
     * @return type
     */
    public function tasks($instanceId)
    {
        $this->prepare();
        //Load the execution data
        $this->processData = $this->loadData($this->bpmnRepository, $instanceId);

        //Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId);

        $links = [];
        foreach ($instance->getTokens() as $token) {
            $links[] = $this->accessLink($token, $instance);
        }

        return $links;
    }

    /**
     * Complete a task
     *
     * @param string $instanceId
     * @param string $tokenId
     * @param array $data
     *
     * @return ExecutionInstanceInterface
     */
    public function completeTask($instanceId, $tokenId, $data = [])
    {
        $this->prepare();
        // Load the execution data
        $this->loadData($this->bpmnRepository, $instanceId);

        // Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId);

        // Update data
        foreach ($data as $key => $value) {
            $instance->getDataStore()->putData($key, $value);
        }

        // Complete task
        foreach ($instance->getTokens() as $token) {
            if ($token->getId() === $tokenId) {
                $task = $this->bpmnRepository->getActivity($token->getProperty('element'));
                $task->complete($token);
                break;
            }
        }
        $this->engine->runToNextState();
        $this->saveState();

        //Return the instance id
        return $instance;
    }

    /**
     * Cancela un proceso por id de instancia.
     *
     * @param string $instanceId
     *
     * @return ExecutionInstanceInterface
     */
    public function cancelProcess($instanceId)
    {
        $this->prepare();
        //Load the execution data
        $processData = $this->loadData($this->bpmnRepository, $instanceId);

        $processData->status = 'CANCELED';
        $processData->save();

        //Return the instance id
        $instance = $this->engine->loadExecutionInstance($instanceId);
        return $instance;
    }

    /**
     * Execute a script task
     *
     * @param string $instanceId
     * @param string $tokenId
     *
     * @return ExecutionInstanceInterface
     */
    public function executeScript($instanceId, $tokenId)
    {
        $this->prepare();
        // Load the execution data
        $model = $this->loadData($this->bpmnRepository, $instanceId);

        // Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId);

        // Complete task
        foreach ($instance->getTokens() as $token) {
            if ($token->getId() === $tokenId) {
                $node = $this->bpmnRepository->findElementById($token->getProperty('element'));
                $task = $this->bpmnRepository->getScriptTask($token->getProperty('element'));
                list($tags, $text) = self::getDocumentationInfo($node);
                $task->setConsoleElement(isset($tags['console'][0]) ? $tags['console'][0] : null);
                $task->setModel($model);
                $task->runScript($token);
                break;
            }
        }
        $this->engine->runToNextState();
        $this->saveState();

        //Return the instance id
        return $instance;
    }

    /**
     * Carga un proceso BPMN
     *
     * @param string $processName
     *
     * @return ProcessInterface
     */
    private function loadProcess($processName)
    {
        foreach ($this->getProcessPaths() as $name => $filename) {
            if ($processName === $name) {
                $this->bpmn = $processName;
                $this->bpmnRepository->load($filename);
                $process = $this->bpmnRepository->getElementsByTagName('process')->item(0)->getBpmnElementInstance();
                return $process;
            }
        }
    }

    /**
     * Get array of all registered processes
     *
     * @return array
     */
    public function getProcessPaths()
    {
        $paths = [];
        foreach (config('workflow.processes') as $path) {
            foreach (glob($path) as $filename) {
                $paths[basename($filename)] = $filename;
            }
        }
        return $paths;
    }

    /**
     * Carga los datos de la instancia almacenados en la BD.
     *
     * @param BpmnDocument $repository
     * @param type $instanceId
     *
     * @return Process
     */
    private function loadData(BpmnDocument $repository, $instanceId)
    {
        $executionInstanceRepository = $this->engine->getRepository()->createExecutionInstanceRepository($repository);
        $processData = Process::findOrFail($instanceId);
        $this->loadProcess($processData->bpmn);
        $executionInstanceRepository->setRawData([
            $instanceId => [
                'id' => $instanceId,
                'processId' => $processData->process_id,
                'data' => json_decode(json_encode($processData->data), true),
                'tokens' => $processData->tokens,
            ]
        ]);
        return $processData;
    }

    private function listenSaveEvents()
    {
        $this->dispatcher->listen(
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            function (ScriptTaskInterface $scriptTask, TokenInterface $token) {
                ScriptTaskJob::dispatch($token);
            }
        );
    }

    /**
     * Save the instance state (tokens)
     *
     * @return void
     */
    private function saveState()
    {
        $processes = $this->bpmnRepository->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'process');
        foreach ($processes as $node) {
            $process = $node->getBpmnElementInstance();
            foreach ($process->getInstances() as $instance) {
                $this->saveProcessInstance($instance);
            }
        }
    }

    /**
     * Save the state of the process instance
     *
     * @param ExecutionInstance $instance
     *
     * @return self
     */
    private function saveProcessInstance(ExecutionInstance $instance)
    {
        $id = $instance->getId();
        $processData = Process::findOrNew($id);
        if (!$processData->exists) {
            $processData->id = $id;
            $processData->process_id = $instance->getProcess()->getId();
            $processData->bpmn = $this->bpmn;
        }
        $dataStore = $instance->getDataStore();
        $tokens = $instance->getTokens();
        $mtokens = [];
        foreach ($tokens as $token) {
            $element = $token->getOwnerElement();
            $mtokens[$token->getId()] = [
                'element' => $element->getId(),
                'name' => $element->getName(),
                'implementation' => $element->getProperty('implementation'),
                'status' => $token->getStatus(),
                'index' => $token->getIndex(),
            ];
        }
        $processData->tokens = $mtokens;
        $processData->data = $dataStore->getData();
        $processData->save();
        return $this;
    }

    /**
     * Obtiene el access link para la actividad.
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $instance
     *
     * @return array
     */
    private function accessLink(TokenInterface $token, ExecutionInstanceInterface $instance)
    {
        $this->instance = $instance;
        $instanceId = $instance->getId();
        $properties = $token->getProperties();
        $id = $properties['element'];
        $node = $this->bpmnRepository->findElementById($id);
        $task = $this->bpmnRepository->getActivity($id);
        $description = $this->getDocumentation($node);
        $implementation = $node->getAttribute('implementation');
        return [
            'token' => $token->getId(),
            'text' => $task->getName(),
            'icon' => '/images/processes/' . $this->bpmn . '/' . $task->getId() . '.svg',
            'description' => $description,
            'href' => $this->evaluateString($implementation ? $implementation : '/Process/Open/' . $instanceId)
            . sprintf('?instance=%s&token=%s', $instanceId, $token->getId()),
        ];
    }

    private function evaluateCode($code)
    {
        extract($this->instance->getDataStore()->getData());
        return eval("return $code;");
    }

    private function evaluateString($string)
    {
        return preg_replace_callback(
            '/\{(.+)\}/',
            function ($match) {
                return $this->evaluateCode($match[1]);
            },
            $string
        );
    }

    /**
     * Read tags from a BPMN node documentation
     *
     * @param BpmnElement $node
     *
     * @return array
     */
    public static function getDocumentationInfo(BpmnElement $node)
    {
        $tags = [];
        $text = '';
        $documentation = $node->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'documentation');
        foreach ($documentation as $doc) {
            $text .= preg_replace_callback('/@(\w+)\(([^()]+)\)/', function ($match) use (&$tags) {
                $tag = $match[1];
                $value = $match[2];
                $tags[$tag][] = $value;
                return '';
            }, $doc->textContent);
        }
        return [$tags, $text];
    }
}
