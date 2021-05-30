<?php

namespace JDD\Workflow\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Workflow Manager Facade
 *
 * @author David Callizaya <davidcallizaya@gmail.com>
 * @see \JDD\Workflow\Bpmn\Manager
 *
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface loadInstance(string $instanceId)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface cancelProcess(string $instanceId)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface startProcess(string $processURL, string $eventId, array $data)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface callProcess(string $processURL, array $data, string $processId)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface completeTask(string $instanceId, string $tokenId, array $data)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface sendMessage(string $instanceId, string $targetId, string $messageId, array $data = [], Model $user = null)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface updateData(string $instanceId, string $tokenId, array $data)
 * @method static \ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface executeScript(string $instanceId, string $tokenId)
 * @method static \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface getElementById(string $bpmn, string $elementId = null)
 * @method static array getProcessPaths()
 * @method static string getProcessSvg(string $processName)
 * loadInstance($instanceId)
 */
class Workflow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'workflow.engine';
    }
}
