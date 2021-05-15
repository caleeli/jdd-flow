<?php

namespace JDD\Workflow\Bpmn\ScriptFormats;

use JDD\Workflow\Bpmn\ScriptTask;
use JDD\Workflow\Bpmn\Token;
use JDD\Workflow\Models\ProcessInstance;

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
    public function runFile(ScriptTask $scriptTask, ProcessInstance $model, Token $token)
    {
        $self = $this;
        $closure = function (ScriptTask $scriptTask, ProcessInstance $model, Token $token) use ($self) {
            $instance = $token->getInstance();
            $data = $token->getInstance()->getDataStore()->getData();
            $response = require $self->filename;
            $instance->getDataStore()->setData($data);
            return $response;
        };
        return $closure->call($scriptTask, $scriptTask, $model, $token);
    }
}
