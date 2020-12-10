<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use JDD\Workflow\Models\Process;

class ProcessUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var Process */
    public $process;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new PrivateChannel('Process.' . $this->process->getKey())];
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
        $tasks = $this->process->tasks();
        return [
            'tasks' => $tasks,
        ];
    }
}
