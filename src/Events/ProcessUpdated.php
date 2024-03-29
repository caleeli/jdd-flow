<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JDD\Workflow\Models\ProcessInstance;

/**
 * @method static ProcessUpdated dispatch(ProcessInstance $process)
 */
class ProcessUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var string */
    public $instanceId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ProcessInstance $process)
    {
        $this->instanceId = $process->getKey();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new PrivateChannel('Process.' . $this->instanceId)];
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'ProcessUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->instanceId,
        ];
    }
}
