<?php

namespace JDD\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use JDD\Workflow\Bpmn\Token;
use JDD\Workflow\Models\Process;
use JDD\Workflow\Models\ProcessToken;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

class TaskAssignedEvent implements ShouldBroadcastNow
{

    public $instance_id;
    public $token_id;
    public $user_id;
    public $implementation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Token $token)
    {
        $this->instance_id = $token->getInstance()->getId();
        $this->token_id = $token->getId();
        $this->user_id = $token->getUserId();
        $this->implementation = $token->getImplementation();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel("Process.{$this->instance_id}"),
            new PrivateChannel("User.{$this->user_id}"),
        ];
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'TaskAssigned';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->token_id,
            'attributes' => [
                'instance_id' => $this->instance_id,
                'user_id' => $this->user_id,
                'implementation' => $this->implementation,
            ]
        ];
    }
}
