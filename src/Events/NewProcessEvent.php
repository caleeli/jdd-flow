<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use JDD\Workflow\Bpmn\ExecutionInstance;
use JDD\Workflow\Models\Process;

class NewProcessEvent implements ShouldBroadcastNow
{

    /** @var Process */
    public $instance_id;
    public $attributes = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExecutionInstance $instance)
    {
        $this->instance_id = $instance->getId();
        $this->attributes = [
            'process' => $instance->getProcess()->getId(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new PrivateChannel('Bpmn')];
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'NewProcess';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->instance_id,
            'attributes' => $this->attributes,
        ];
    }
}
