<?php

namespace JDD\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use JDD\Workflow\Bpmn\ExecutionInstance;
use JDD\Workflow\Facades\Workflow;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Process model
 *
 * Swagger definition:
 *
 *  @OA\Schema(
 *      schema="ProcessEditable",
 *      @OA\Property(
 *          property="attributes",
 *          type="object",
 *          @OA\Property(property="definitions", type="string"),
 *          @OA\Property(property="data", type="object"),
 *          @OA\Property(
 *              property="tokens",
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="element", type="string"),
 *                  @OA\Property(property="name", type="string"),
 *                  @OA\Property(property="implementation", type="string"),
 *                  @OA\Property(property="status", type="string"),
 *              )
 *          ),
 *          @OA\Property(property="status", type="string", enum={"ACTIVE", "COMPLETED"}),
 *      )
 *  )
 *
 *  @OA\Schema(
 *      schema="Process",
 *      allOf={
 *          @OA\Schema(
 *              @OA\Property(property="id", type="string", format="id"),
 *          ),
 *          @OA\Schema(ref="#/components/schemas/ProcessEditable"),
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="attributes",
 *                  type="object",
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time"),
 *                  @OA\Property(property="id", type="string", format="id"),
 *              )
 *          )
 *      }
 *  )
 *
 * @property string $definitions
 * @property object $name
 * @property object $data
 * @property string $status Status of the process instance
 * @property ProcessAction[] $actions
 * @property ProcessToken[] $tokens
 * @property ProcessToken[] $active_tokens
 *
 * @method static ProcessInstance find(string $id)
 * @method static ProcessInstance findOrNew(string $id)
 * @method mixed save()
 */
class ProcessInstance extends Model
{
    protected $table = 'process_instances';
    protected $attributes = [
        'definitions' => '',
        'name' => '',
        'data' => '{}',
        'props' => '{}',
        'status' => 'ACTIVE',
    ];
    protected $fillable = [
        'definitions',
        'name',
        'data',
        'props',
        'status',
    ];
    protected $casts = [
        'props' => 'array',
    ];

    /**
     * Get data array
     *
     * @param string $value
     *
     * @return array
     */
    public function getDataAttribute($value)
    {
        return json_decode($value, \true);
    }

    /**
     * Set data object
     *
     * @param object|array $value
     *
     * @return void
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode((object) $value);
    }

    /**
     * Call a process with the workflow engine
     *
     * @param string $bpmn
     * @param string $processId
     * @param array $data
     *
     * @return array
     */
    public function callProcess($bpmn, $processId, array $data)
    {
        $instance = Workflow::callProcess($bpmn, $data, $processId);
        return [
            'id' => $instance->getId(),
            'attributes' => $instance->getProperties(),
        ];
    }

    /**
     * Start a process by startEvent with the workflow engine
     *
     * @param array $data
     *
     * @return array
     */
    public function start($process, $start, array $data)
    {
        $instance = Workflow::startProcess($process, $start, $data);
        return [
            'id' => $instance->getId(),
            'attributes' => $instance->getProperties(),
        ];
    }

    /**
     * Cancell a process instance
     *
     * @param array $data
     *
     * @return array
     */
    public function cancel()
    {
        $instance = Workflow::cancelProcess($this->id);
        return [
            'id' => $instance->getId(),
            'attributes' => $instance->getProperties(),
        ];
    }

    /**
     * Get active task in process
     *
     * @return array
     */
    public function tasks()
    {
        $tasks = [];
        foreach ($this->tokens as $token) {
            if ($token['status'] !== 'CLOSED') {
                $tasks[] = [
                    'name' => $token['name'] ?? '',
                    'status' => $token['status'],
                    'implementation' => $token['implementation'] ?? null,
                    'token' => [
                        'instance' => $this->id,
                        'token' => $token['id'],
                    ],
                ];
            }
        }
        return $tasks;
    }

    public function tokens()
    {
        return $this->hasMany(ProcessToken::class, 'instance_id');
    }

    public function active_tokens()
    {
        return $this->hasMany(ProcessToken::class, 'instance_id')->where('status', 'ACTIVE');
    }

    public function setTokensAttribute(array $tokens)
    {
        if ($this->exists) {
            $current = $this->tokens()->get();
            $ids = [];
            foreach ($tokens as $token) {
                if (!$token['status']) {
                    continue;
                }
                $ids[] = $token['id'];
                $t = $current->find($token['id']);
                if (!$t) {
                    $t = $this->tokens()->make();
                }
                foreach ($token as $k => $v) {
                    $t->$k = $v;
                }
                $t->definitions = $this->definitions;
                $t->save();
            }
            $this->tokens()->whereNotIn('id', $ids)->update(['status' => 'CLOSED']);
        } else {
            static::created(function ($issue) use ($tokens) {
                $issue->tokens = $tokens;
            });
        }
    }

    /**
     * Filter for user logged
     *
     * @param mixed $query
     *
     * @return mixed
     */
    public function scopeWhereUserLogged($query)
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Return process properties
     *
     * @param string $bpmn
     * @param string $processId
     *
     * @return array
     */
    public function getProcess($bpmn, $processId)
    {
        return Workflow::getElementById($bpmn, $processId)->getProperties();
    }

    /**
     * Get screen for the user id
     *
     * @property string $userId
     *
     * @return string
     */
    public function getScreen($userId = null)
    {
        if (!$userId) {
            $userId = Auth::id();
        }
        $token = $this->tokens()->where('user_id', $userId)->orderBy('id', 'desc')->limit(1)->first();
        if ($token) {
            return $token->getScreen();
        }
        return '';
    }

    /**
     * Process actions
     *
     * @return HasMany
     */
    public function actions()
    {
        return $this->hasMany(ProcessAction::class);
    }

    /**
     * Get actions (events) that could be executed in the process instance
     *
     * @param ExecutionInstanceInterface $instance
     *
     * @return array
     */
    public function getActions(ExecutionInstanceInterface $instance = null)
    {
        if (!$instance) {
            $instance = Workflow::loadInstance($this->id);
        }
        $actions = [];
        $boundaryEvents = $instance->getProcess()->getOwnerDocument()->getElementsByTagName('boundaryEvent');
        foreach ($boundaryEvents as $boundaryEvent) {
            $actions = $this->addActionsFromBoundaryEvent(
                $instance,
                $this,
                $boundaryEvent->getBpmnElementInstance(),
                $actions
            );
        }
        return $actions;
    }

    private function addActionsFromBoundaryEvent(ExecutionInstance $instance, ProcessInstance $model, BoundaryEventInterface $boundary, array $actions)
    {
        $tokens = $boundary->getAttachedTo()->getTokens($instance);
        foreach ($tokens as $token) {
            if ($token->getStatus() === 'CLOSED') {
                continue;
            }
            foreach ($boundary->getEventDefinitions() as $event) {
                $payload = $event->getPayload();
                if (!$payload) {
                    continue;
                }
                $actions[] = [
                    'process_instance_id' => $instance->getId(),
                    'process_token_id' => $token->getId(),
                    'definitions' => $model->definitions,
                    'element' => $boundary->getId(),
                    'event' => $payload->getId(),
                    'name' => $instance->trans($payload->getProperty('name')),
                ];
            }
        }
        return $actions;
    }

    /**
     * Send a message into a process instance
     *
     * @param string $targetId
     * @param string $messageId
     * @param array $data
     *
     * @return void
     */
    public function sendMessage($targetId, $messageId, array $data = [])
    {
        Workflow::sendMessage($this->id, $targetId, $messageId, $data);
    }
}
