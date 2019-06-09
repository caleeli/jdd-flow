<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use JDD\Workflow\Bpmn\ScriptTask;

class ScriptConsole implements ShouldBroadcastNow
{
    use SerializesModels;

    public $consoleTokens = [];
    public $instanceId;
    public $tokenId;
    public $logfile;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TokenInterface $token, ScriptTask $script, $logfile)
    {
        $instance = $token->getInstance();
        $this->instanceId = $instance->getId();
        $this->tokenId = $token->getId();
        $this->logfile = $logfile;
        $task = $script->getConsoleElement();
        foreach ($instance->getTokens() as $tokenId => $token) {
            $properties = $token->getProperties();
            if ($properties['element'] === $task && $token->getStatus() === 'ACTIVE') {
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
        return 'ScriptConsole';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['id' => $this->instanceId, 'token' => $this->tokenId, 'url' => ('/storage/' . $this->logfile)];
    }
}
