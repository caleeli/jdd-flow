<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Models\Process;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityClosedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCreatedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask;
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

        //Se podria mover al app service provider
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
        //Process
        $process = $this->loadProcess($processURL);
        $instance = $process->call();
        $this->engine->runToNextState();
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
        return $instance;
    }

    /**
     * Obtiene la lista de tareas del proceso para el usuario actual.
     *
     * @return type
     */
    public function tasks($instanceId)
    {
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
        //Load the execution data
        $processData = $this->loadData($this->bpmnRepository, $instanceId);

        $processData->status = 'CANCELED';
        $processData->save();

        //Return the instance id
        $instance = $this->engine->loadExecutionInstance($instanceId);
        return $instance;
    }

    public function executeScript($instanceId, $tokenId)
    {
        // Load the execution data
        $this->loadData($this->bpmnRepository, $instanceId);

        // Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId);

        // Complete task
        foreach ($instance->getTokens() as $token) {
            if ($token->getId() === $tokenId) {
                $node = $this->bpmnRepository->findElementById($token->getProperty('element'));
                $task = $this->bpmnRepository->getScriptTask($token->getProperty('element'));
                list($tags, $text) = self::getDocumentationInfo($node);
                error_log('console: ' . json_encode($tags['console'][0]));
                $task->setConsoleElement(isset($tags['console'][0]) ? $tags['console'][0] : null);
                $task->runScript($token);
                break;
            }
        }
        $this->engine->runToNextState();

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
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            function (ProcessInstanceCreatedEvent $payload) {
                $processData = Process::findOrNew($payload->instance->getId());
                $dataStore = $payload->instance->getDataStore();
                $processData->process_id = $payload->instance->getProcess()->getId();
                $processData->data = $dataStore->getData();
                $processData->tokens = [];
                $processData->status = 'ACTIVE';
                $processData->bpmn = $this->bpmn;
                $processData->save();
                $payload->instance->setId($processData->id);
            }
        );
        $this->dispatcher->listen(
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            function (ProcessInstanceCompletedEvent $payload) {
                $processData = Process::findOrFail($payload->instance->getId());
                $processData->status = 'COMPLETED';
                $processData->save();
                $payload->instance->setId($processData->id);
            }
        );
        $this->dispatcher->listen(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function (ActivityActivatedEvent $event) {
                $id = $event->token->getInstance()->getId();
                $processData = Process::findOrFail($id);
                $dataStore = $event->token->getInstance()->getDataStore();
                $tokens = $processData->tokens;
                $tokens[$event->token->getId()] = [
                    'element' => $event->activity->getId(),
                    'name' => $event->activity->getName(),
                    'implementation' => $event->activity->getProperty('implementation'),
                    'status' => $event->token->getStatus(),
                ];
                $processData->tokens = $tokens;
                $processData->data = $dataStore->getData();
                $processData->save();
            }
        );
        $this->dispatcher->listen(
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            function (ScriptTaskInterface $scriptTask, TokenInterface $token) {
                $id = $token->getInstance()->getId();
                $processData = Process::findOrFail($id);
                $dataStore = $token->getInstance()->getDataStore();
                $tokens = $processData->tokens;
                $tokens[$token->getId()] = [
                    'element' => $scriptTask->getId(),
                    'name' => $scriptTask->getName(),
                    'implementation' => $scriptTask->getProperty('implementation'),
                    'status' => $token->getStatus(),
                ];
                $processData->tokens = $tokens;
                $processData->data = $dataStore->getData();
                $processData->save();
                ScriptTaskJob::dispatch($token);
            }
        );
        $this->dispatcher->listen(
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            function (ActivityCompletedEvent $event) {
                $id = $event->token->getInstance()->getId();
                $processData = Process::findOrFail($id);
                $dataStore = $event->token->getInstance()->getDataStore();
                $tokens = $processData->tokens;
                $tokens[$event->token->getId()] = [
                    'element' => $event->activity->getId(),
                    'name' => $event->activity->getName(),
                    'implementation' => $event->activity->getProperty('implementation'),
                    'status' => $event->token->getStatus(),
                ];
                $processData->tokens = $tokens;
                $processData->data = $dataStore->getData();
                $processData->save();
            }
        );
        $this->dispatcher->listen(
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            function (ActivityClosedEvent $event) {
                $id = $event->token->getInstance()->getId();
                $processData = Process::findOrFail($id);
                $dataStore = $event->token->getInstance()->getDataStore();
                $tokens = $processData->tokens;
                $tokens[$event->token->getId()] = [
                    'element' => $event->activity->getId(),
                    'name' => $event->activity->getName(),
                    'implementation' => $event->activity->getProperty('implementation'),
                    'status' => $event->token->getStatus(),
                ];
                $processData->tokens = $tokens;
                $processData->data = $dataStore->getData();
                $processData->save();
            }
        );
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
