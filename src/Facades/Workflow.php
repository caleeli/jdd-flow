<?php

namespace JDD\Workflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Workflow Manager Facade
 *
 * @author David Callizaya <davidcallizaya@gmail.com>
 * @see \JDD\Workflow\Bpmn\Manager
 *
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface cancelProcess(string $instanceId)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface startProcess(string $processURL, string $eventId, array $data)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface callProcess(string $processURL, array $data, string $processId)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface completeTask(string $instanceId, string $tokenId, array $data)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface updateData(string $instanceId, string $tokenId, array $data)
 * @method static array getProcessPaths()
 * @method static string getProcessSvg(string $processName)
 */
class Workflow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'workflow.engine';
    }
}
