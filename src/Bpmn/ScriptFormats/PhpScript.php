<?php

namespace JDD\Workflow\Bpmn\ScriptFormats;

use JDD\Workflow\Bpmn\ScriptTask;

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
    public function runFile(ScriptTask $scriptTask, $model)
    {
        $self = $this;
        $closure = function (ScriptTask $scriptTask, $model) use ($self) {
            return require $self->filename;
        };
        return $closure->call($scriptTask, $scriptTask, $model);
    }
}
