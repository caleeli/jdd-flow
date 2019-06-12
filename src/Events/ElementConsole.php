<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class ElementConsole implements ShouldBroadcastNow
{
    use SerializesModels;

    public $consoleTokens = [];
    public $instanceId;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ExecutionInstanceInterface $instance, $elementId, $message)
    {
        $this->instanceId = $instance->getId();
        $this->message = $message;
        foreach ($instance->getTokens() as $token) {
            $properties = $token->getProperties();
            if ($properties['element'] === $elementId && $token->getStatus() === 'ACTIVE') {
                $this->consoleTokens[] = $token->getId();
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        foreach ($this->consoleTokens as $token) {
            $channels[] = new PrivateChannel('Process.' . $this->instanceId . '.Token.' . $token);
        }
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'ElementConsole';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->message;
    }
}
