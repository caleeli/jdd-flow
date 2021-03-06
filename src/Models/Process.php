<?php

namespace JDD\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use JDD\Workflow\Facades\Workflow;

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
 *          @OA\Property(property="bpmn", type="string"),
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
 */
class Process extends Model
{
    protected $attributes = [
        'bpmn' => '',
        'data' => '{}',
        'tokens' => '{}',
        'status' => 'ACTIVE',
    ];
    protected $fillable = [
        'bpmn',
        'data',
        'tokens',
        'status',
    ];
    protected $casts = [
        'tokens' => 'array',
    ];

    /**
     * Get data object
     *
     * @param string $value
     *
     * @return object
     */
    public function getDataAttribute($value)
    {
        return json_decode($value);
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
     * @param array $data
     *
     * @return array
     */
    public function call($processUrl, array $data)
    {
        $instance = Workflow::callProcess($processUrl, $data);
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
    public function start($processUrl, $start, array $data)
    {
        $instance = Workflow::startProcess($processUrl, $start, $data);
        return [
            'id' => $instance->getId(),
            'attributes' => $instance->getProperties(),
        ];
    }

    /**
     * Complete a task of the process instance
     *
     * @param array $data
     *
     * @return array
     */
    public function completeTask($token, array $data)
    {
        $instance = Workflow::completeTask($this->id, $token, $data);
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
                    'path' => $token['implementation'] ? substr($token['implementation'], 1) : null,
                    'token' => [
                        'instance' => $this->id,
                        'token' => $token['id'],
                    ],
                ];
            }
        }
        return $tasks;
    }
}
