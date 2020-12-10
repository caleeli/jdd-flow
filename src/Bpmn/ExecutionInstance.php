<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Models\Process;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;
use ProcessMaker\Nayra\Engine\ExecutionInstanceTrait;

/**
 * Execution instance for the engine.
 *
 * @package ProcessMaker\Models
 */
class ExecutionInstance implements ExecutionInstanceInterface
{
    use ExecutionInstanceTrait;

    /**
     * @var Process
     */
    private $_model;

    /**
     * Initialize a token class with unique id.
     *
     */
    protected function initToken()
    {
        $this->setId(hexdec(uniqid()));
    }

    /**
     * Get process model of this instance
     *
     * @return Process
     */
    public function getModel()
    {
        $this->_model = $this->_model ?: Process::find($this->getId());
        return $this->_model;
    }
}
