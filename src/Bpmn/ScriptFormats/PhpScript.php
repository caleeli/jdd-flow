<?php

namespace JDD\Workflow\Bpmn\ScriptFormats;

use JDD\Workflow\Bpmn\ScriptTask;
use JDD\Workflow\Bpmn\Token;
use JDD\Workflow\Models\Process;

class PhpScript extends BaseScriptExecutor
{
    /**
     * Run a file with the script code
     *
     * @param ScriptTask $scriptTask
     * @param mixed $model
     *
     * @return mixed
     */
    public function runFile(ScriptTask $scriptTask, Process $model, Token $token)
    {
        $self = $this;
        $closure = function (ScriptTask $scriptTask, Process $model, Token $token) use ($self) {
            $instance = $token->getInstance();
            $data = $token->getInstance()->getDataStore()->getData();
            return require $self->filename;
        };
        return $closure->call($scriptTask, $scriptTask, $model, $token);
    }
}
