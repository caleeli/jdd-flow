<?php

namespace JDD\Workflow\Bpmn;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use JDD\Workflow\Events\ProcessUpdated;
use JDD\Workflow\Models\ProcessInstance;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Execution Instance Repository.
 *
 * @package ProcessMaker\Models
 */
class ExecutionInstanceRepository implements ExecutionInstanceRepositoryInterface
{
    /**
     * Array to simulate a storage of execution instances.
     *
     * @var array $data
     */
    private static $data = [];

    /**
     * Load an execution instance from a persistent storage.
     *
     * @param string $uid
     * @param StorageInterface $storage
     *
     * @return null|ExecutionInstanceInterface
     */
    public function loadExecutionInstanceByUid($uid, StorageInterface $storage)
    {
        if (empty(self::$data) || empty(self::$data[$uid])) {
            return;
        }
        $data = self::$data[$uid];
        $instance = $this->createExecutionInstance();
        $instance->setProperties($data['props']);
        $instance->setId($uid);
        $instance->setName($data['name']);
        $instance->setProperty('status', $data['status']);
        $process = $storage->getProcess($data['processId']);
        $dataStore = $storage->getFactory()->createDataStore();
        $dataStore->setData($data['data']);
        $instance->setProcess($process);
        $instance->setDataStore($dataStore);
        $process->getTransitions($storage->getFactory());

        //Load tokens:
        foreach ($data['tokens'] as $tokenInfo) {
            $token = $storage->getFactory()->getTokenRepository()->createTokenInstance();
            $token->setProperties($tokenInfo);
            $element = $storage->getElementInstanceById($tokenInfo['element']);
            $element->addToken($instance, $token);
        }
        return $instance;
    }

    /**
     * Save an instance
     *
     * @param ExecutionInstance $instance
     */
    public function saveProcessInstance(ExecutionInstance $instance, $bpmn)
    {
        $id = $instance->getId();
        $processData = ProcessInstance::findOrNew($id);
        if (!$processData->exists) {
            $processData->id = $id;
            $processData->process = $instance->getProcess()->getId();
            $processData->definitions = $bpmn;
            $processData->user_id = Auth::id();
        }
        $processData->props = $instance->getProperties();
        $processData->status = $instance->getProperty('status', 'ACTIVE');
        $dataStore = $instance->getDataStore();
        $tokens = $instance->getTokens();
        $data = $instance->getDataStore()->getData();
        $mtokens = [];
        foreach ($tokens as $token) {
            $element = $token->getOwnerElement();
            $mtokens[] = [
                'id' => $token->getId(),
                'element' => $element->getId(),
                'name' => $instance->trans($element->getName(), $data),
                'type' => $element->getBpmnElement()->localName,
                'implementation' => $token->getImplementation(),
                'user_id' => $token->getProperty('user_id'),
                'status' => $token->getStatus(),
                'index' => $token->getIndex(),
                'log' => $token->getProperty('log'),
            ];
        }
        $processData->tokens = $mtokens;
        $processData->name = $instance->getName();
        $processData->data = $dataStore->getData();
        $processData->save();
        // Save actions
        $this->saveActions($instance, $processData);
        // triggers event on this save. Observer did not trigger it if called twice in a http request
        ProcessUpdated::dispatch($processData);
    }

    /**
     * Parse documentation node to get a tag content
     *
     * @param EntityInterface $element
     * @param string $tag
     * @param array $data
     *
     * @return string
     */
    private function parseDocumentation(EntityInterface $element, $tag, array $data = [])
    {
        $documentation = $element->getBpmnElement()
            ->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'documentation');
        foreach ($documentation as $doc) {
            if (strpos($doc->textContent, "$tag:") === 0) {
                $text = trim(substr($doc->textContent, strlen($tag) + 1));
                return $this->bladeText($text, $data);
            }
        }
        return '';
    }

    /**
     * Blade $text with $data
     *
     * @param string $text
     * @param array $data
     *
     * @return string
     */
    private function bladeText($text, array $data = [])
    {
        $generated = Blade::compileString($text);
        ob_start() and extract($data, EXTR_SKIP);
        try {
            eval('?>'.$generated);
        } catch (Exception $e) {
            ob_get_clean();
            return '';
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Set the test data to be loaded.
     *
     * @param array $data
     */
    public function setRawData(array $data)
    {
        self::$data = $data;
    }

    /**
     * Creates an execution instance.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstance
     */
    public function createExecutionInstance()
    {
        return new ExecutionInstance();
    }

    /**
     * Persists instance's data related to the event Process Instance Created
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCreated(ExecutionInstanceInterface $instance)
    {
    }

    /**
     * Persists instance's data related to the event Process Instance Completed
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return mixed
     */
    public function persistInstanceCompleted(ExecutionInstanceInterface $instance)
    {
        $id = $instance->getId();
        $process = ProcessInstance::find($id);
        if ($process) {
            $status = $instance->getProperty('status');
            if ($status === 'ACTIVE') {
                $status = 'COMPLETED';
                $instance->setProperty('status', $status);
            }
            $process->props = $instance->getProperties();
            $process->status = $status;
            $process->save();
            ProcessUpdated::dispatch($process);
        }
    }

    /**
     * Persists collaboration between two instances.
     *
     * @param ExecutionInstanceInterface $target Target instance
     * @param ParticipantInterface $targetParticipant Participant related to the target instance
     * @param ExecutionInstanceInterface $source Source instance
     * @param ParticipantInterface $sourceParticipant
     */
    public function persistInstanceCollaboration(ExecutionInstanceInterface $target, ParticipantInterface $targetParticipant, ExecutionInstanceInterface $source, ParticipantInterface $sourceParticipant)
    {
    }

    private function saveActions(ExecutionInstanceInterface $instance, ProcessInstance $model)
    {
        $actions = $model->getActions($instance);
        $model->actions()->delete();
        $model->actions()->createMany($actions);
    }
}
