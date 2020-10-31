<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use JDD\Workflow\Models\Process;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class NewProcessEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var Process */
    public $instance_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExecutionInstanceInterface $instance)
    {
        \Log::info('NewProcess ' . $instance->getId());
        $this->instance_id = $instance->getId();
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
            'instance_id' => $this->instance_id,
        ];
    }
}
