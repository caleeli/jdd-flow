<?php

namespace JDD\Worlflow\Models;

use Illuminate\Database\Eloquent\Model;
use JDD\Worlflow\Facades\Workflow;

class Process extends Model
{
    protected $attributes = [
        'bpmn' => '',
        'data' => '{}',
        'tokens' => '{}',
        'status' => 'ACTIVE',
    ];
    protected $fillable = [
        'data',
        'tokens',
        'status',
    ];
    protected $casts = [
        'data' => 'array',
        'tokens' => 'array',
    ];

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
    public function cancelProcess()
    {
        $instance = Workflow::cancelProcess($this->id);
        return [
            'attributes' => $instance->getProperties(),
        ];
    }
}
