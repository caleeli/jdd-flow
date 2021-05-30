<?php

namespace JDD\Workflow\Bpmn;

use Illuminate\Database\Eloquent\Model;
use JDD\Workflow\Bpmn\Contracts\HumanPerformerInterface;
use JDD\Workflow\Bpmn\Contracts\PotentialOwnerInterface;
use JDD\Workflow\Bpmn\Contracts\ResourceAssignmentExpressionInterface;
use JDD\Workflow\Bpmn\Contracts\ResourceRoleInterface;
use JDD\Workflow\Events\ActivityActivated;
use JDD\Workflow\Events\NewProcessEvent;
use JDD\Workflow\Events\ProcessInstanceCancelled;
use JDD\Workflow\Events\ProcessInstanceCompleted;
use JDD\Workflow\Exceptions\ProcessNotFoundException;
use JDD\Workflow\Exceptions\TokenNotFoundException;
use JDD\Workflow\Jobs\ScriptTaskJob;
use JDD\Workflow\Models\ProcessInstance;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCompletedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ProcessInstanceCreatedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
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
     *
     * @var ExecutionInstanceRepository
     */
    private $instanceRepository;

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
     * @var ProcessInstance $processData
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
        $this->listenBpmnEvents();
    }

    private function prepare()
    {
        $this->engine->clearInstances();
        //Load a BpmnFile Repository
        $this->bpmnRepository = new BpmnDocument();
        $this->bpmnRepository->setEngine($this->engine);
        $this->bpmnRepository->setFactory($this->repository);
        $mapping = $this->bpmnRepository->getBpmnElementsMapping();
        // Add relationship UserTask-Performer
        $mapping[BpmnDocument::BPMN_MODEL]['userTask'][1]['resources'] = ['n', ResourceRoleInterface::class];
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'userTask',
            $mapping[BpmnDocument::BPMN_MODEL]['userTask'],
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'resourceAssignmentExpression',
            [
                ResourceAssignmentExpressionInterface::class,
                [
                    ResourceAssignmentExpressionInterface::BPMN_PROPERTY_FORMAL_EXPRESSION => ['1', [BpmnDocument::BPMN_MODEL, 'formalExpression']],
                ],
            ],
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'humanPerformer',
            [
                HumanPerformerInterface::class,
                [
                    HumanPerformerInterface::BPMN_PROPERTY_RESOURCE_ASSIGNMENT_EXPRESSION => ['1', [BpmnDocument::BPMN_MODEL, 'resourceAssignmentExpression']],
                ],
            ],
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'potentialOwner',
            [
                PotentialOwnerInterface::class,
                [
                    PotentialOwnerInterface::BPMN_PROPERTY_RESOURCE_ASSIGNMENT_EXPRESSION => ['1', [BpmnDocument::BPMN_MODEL, 'resourceAssignmentExpression']],
                ],
            ],
        );
        $this->bpmnRepository->setBpmnElementMapping(
            BpmnDocument::BPMN_MODEL,
            'formalExpression',
            $mapping[BpmnDocument::BPMN_MODEL]['conditionExpression']
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
        $this->engine->setRepository($this->repository);
        $this->instanceRepository = $this->repository->createExecutionInstanceRepository($this->bpmnRepository);
    }

    /**
     * Call a process
     *
     * @param string $processURL
     * @param array $data
     *
     * @return ExecutionInstanceInterface
     */
    public function callProcess($processURL, $data = [], $processId)
    {
        $this->prepare();
        //Process
        $process = $this->loadProcess($processURL, $processId);
        $dataStore = $this->repository->createDataStore();
        $dataStore->setData($data);
        $instance = $process->call($dataStore);
        $instance->setName($process->getName());
        $this->engine->runToNextState();
        $this->saveState();
        return $instance;
    }

    /**
     * Get process definition
     *
     * @param string $bpmn
     * @param string $elementId
     *
     * @return EntityInterface
     */
    public function getElementById($bpmn, $elementId)
    {
        $this->prepare();
        // Bpmn Element
        foreach ($this->getProcessPaths() as $name => $filename) {
            if ($bpmn === $name) {
                $this->bpmn = $bpmn;
                $this->bpmnRepository->load($filename);
                $element = $this->bpmnRepository->findElementById($elementId)->getBpmnElementInstance();
                return $element;
            }
        }
        return null;
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
        $dataStorage->setData((object) $data);
        $instance = $process->getEngine()->createExecutionInstance(
            $process,
            $dataStorage
        );
        $event->start($instance);
        $instance->setName($event->getProcess()->getName());

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
        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);

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
        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);

        // Token
        $token = $instance->getTokens()->findFirst(function ($token) use ($tokenId) {
            return $token->getId() === $tokenId;
        });
        if (!$token) {
            throw new TokenNotFoundException($tokenId);
        }

        // Update data
        foreach ($data as $key => $value) {
            $instance->getDataStore()->putData($key, $value);
        }

        // Complete task
        $task = $this->bpmnRepository->getActivity($token->getProperty('element'));
        $task->complete($token);

        $this->engine->runToNextState();
        $this->saveState();

        //Return the instance id
        return $instance;
    }

    /**
     * Send a message into an process instance
     *
     * @param string $instanceId
     * @param string $targetId
     * @param string $messageId
     * @param array $data
     *
     * @return ExecutionInstanceInterface
     */
    public function sendMessage($instanceId, $targetId, $messageId, $data = [], Model $user = null)
    {
        $this->prepare();
        // Load the execution data
        $this->loadData($this->bpmnRepository, $instanceId);

        // Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);

        // Update data
        foreach ($data as $key => $value) {
            $instance->getDataStore()->putData($key, $value);
        }

        // Get  target
        $catchEvent = $this->bpmnRepository->getCatchEvent($targetId);

        // Execute event
        foreach($catchEvent->getEventDefinitions() as $ed) {
            if ($ed->getPayload()->getId() === $messageId) {
                $catchEvent->execute($ed, $instance);
            }
        }

        $this->engine->runToNextState();
        $this->saveState();

        //Return the instance id
        return $instance;
    }

    /**
     * Update data from a task
     *
     * @param string $instanceId
     * @param string $tokenId
     * @param array $data
     *
     * @return ExecutionInstanceInterface
     */
    public function updateData($instanceId, $tokenId, $data = [])
    {
        $this->prepare();
        // Load the execution data
        $this->loadData($this->bpmnRepository, $instanceId);

        // Process and instance
        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);

        // Update data
        foreach ($data as $key => $value) {
            $instance->getDataStore()->putData($key, $value);
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
        $this->loadData($this->bpmnRepository, $instanceId);

        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);
        $instance->close();
        $instance->setProperty('status', 'CANCELED');
        $this->engine->runToNextState();
        $this->saveState();
        ProcessInstanceCancelled::dispatch($instance);
        return $instance;
    }

    /**
     * Load an instance by id.
     *
     * @param string $instanceId
     *
     * @return ExecutionInstanceInterface
     */
    public function loadInstance($instanceId)
    {
        $this->prepare();
        //Load the execution data
        $this->loadData($this->bpmnRepository, $instanceId);

        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);
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
        $instance = $this->engine->loadExecutionInstance($instanceId, $this->bpmnRepository);

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
     * @param string $bpmn
     *
     * @return ProcessInterface
     */
    private function loadProcess($bpmn, $processId = null)
    {
        foreach ($this->getProcessPaths() as $name => $filename) {
            if ($bpmn === $name) {
                $this->bpmn = $bpmn;
                $this->bpmnRepository->load($filename);
                if (!$processId) {
                    $process = $this->bpmnRepository->getElementsByTagName('process')->item(0)->getBpmnElementInstance();
                } else {
                    $process = $this->bpmnRepository->findElementById($processId)->getBpmnElementInstance();
                }
                return $process;
            }
        }
        throw new ProcessNotFoundException($bpmn, $processId);
    }

    /**
     * Get array of all registered processes
     *
     * @return array
     */
    public function getProcessPaths()
    {
        $paths = [];
        foreach (config('workflow.processes', []) as $path) {
            foreach (glob($path) as $filename) {
                $paths[basename($filename)] = $filename;
            }
        }
        return $paths;
    }

    /**
     * Get process path
     *
     * @param string $bpmn
     *
     * @return string
     */
    public function getProcessPath($bpmn)
    {
        foreach (config('workflow.processes', []) as $path) {
            foreach (glob($path) as $filename) {
                if ($bpmn === basename($filename)) {
                    return realpath($filename);
                }
            }
        }
        return null;
    }

    /**
     * Get the process svg representation
     *
     * @param string $process
     *
     * @return string
     */
    public function getProcessSvg($processName)
    {
        foreach ($this->getProcessPaths() as $name => $filename) {
            if ($processName === $name) {
                $path = realpath($filename);
                $info = pathinfo($path);
                $svgfilename = $info['dirname'] . '/' . substr($info['basename'], 0, -strlen($info['extension'])) . 'svg';
                return file_exists($svgfilename) ? $svgfilename : null;
            }
        }
    }

    /**
     * Carga los datos de la instancia almacenados en la BD.
     *
     * @param BpmnDocument $repository
     * @param type $instanceId
     *
     * @return ProcessInstance
     */
    private function loadData(BpmnDocument $repository, $instanceId)
    {
        $processData = ProcessInstance::findOrFail($instanceId);
        $this->loadProcess($processData->definitions);
        $this->instanceRepository->setRawData([
            $instanceId => [
                'id' => $instanceId,
                'processId' => $processData->process,
                'name' => $processData->name,
                'status' => $processData->status,
                'data' => json_decode(json_encode($processData->data), true),
                'props' => json_decode(json_encode($processData->props ?: []), true),
                'tokens' => $processData->tokens()->where('status', '!=', 'CLOSED')->get()->toArray(),
            ]
        ]);
        return $processData;
    }

    /**
     * Listen for Workflow events
     *
     */
    private function listenBpmnEvents()
    {
        $this->dispatcher->listen(
            ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED,
            function (ScriptTaskInterface $scriptTask, TokenInterface $token) {
                $this->saveProcessInstance($token->getInstance());
                ScriptTaskJob::dispatch($token);
            }
        );
        $this->dispatcher->listen(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function (ActivityActivatedEvent $event) {
                $resources = $event->activity->getProperty('resources');
                $this->assignResource($event->token, $resources, $event->activity);
                $this->saveProcessInstance($event->token->getInstance());
                ActivityActivated::dispatch($event->token);
            }
        );
        //$this->dispatcher->listen(
        //    ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
        //    function (ActivityInterface $activity, TokenInterface $token) {
        //        $resources = $event->activity->getProperty('resources');
        //        $this->assignResource($event->token, $resources, $event->activity);
        //        $this->saveProcessInstance($event->token->getInstance());
        //        ActivityActivated::dispatch($event->token);
        //    }
        //);
        $this->dispatcher->listen(
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            function (ProcessInstanceCreatedEvent $event) {
                event(new NewProcessEvent($event->instance));
            }
        );
        $this->dispatcher->listen(
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            function (ProcessInstanceCompletedEvent $event) {
                ProcessInstanceCompleted::dispatch($event->instance);
            }
        );
        $this->dispatcher->listen(
            ErrorEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            function ($element, TokenInterface $innerToken, ErrorEventDefinitionInterface $error) {
                $instance = $innerToken->getInstance();
                $instance->setProperty('status', 'FAILED');
                $instance->setProperty('error', [
                    'element_name' => $element->getName(),
                    'element_id' => $element->getId(),
                    'error' => $error->getName(),
                ]);
            }
        );
    }

    /**
     * Assign resource to activity
     *
     * @param TokenInterface $token
     * @param ResourceRoleInterface[]|null $resources
     * @param ActivityInterface $activity
     *
     * @return void
     */
    private function assignResource(TokenInterface $token, Collection $resources = null, ActivityInterface $activity)
    {
        if (!$resources) {
            return;
        }
        foreach ($resources as $resource) {
            $resource->execute($token, $activity);
        }
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
    private function saveProcessInstance(ExecutionInstanceInterface $instance)
    {
        $this->instanceRepository->saveProcessInstance($instance, $this->bpmn);
        return $this;
    }

    /**
     * Obtiene el access link para la actividad.
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $instance
     *
     * @deprecated
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
