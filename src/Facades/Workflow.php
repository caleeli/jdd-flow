<?php

namespace JDD\Workflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Workflow Manager Facade
 *
 * @author David Callizaya <davidcallizaya@gmail.com>
 * @see \JDD\Workflow\Bpmn\Manager
 *
 * @method \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface cancelProcess(string $instanceId)
 * @method \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface startProcess(string $processURL, string $eventId, array $data)
 * @method \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface callProcess(string $processURL, array $data)
 * @method \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface completeTask(string $instanceId, string $tokenId, array $data)
 */
class Workflow extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'workflow.engine';
    }
}
