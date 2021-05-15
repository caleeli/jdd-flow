<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Models\ProcessInstance;
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
     * @var ProcessInstance
     */
    private $_model;

    /**
     * Initialize a token class with unique id.
     *
     */
    protected function initToken()
    {
        $this->setId(IdGenerator::newInt());
    }

    /**
     * Get process model of this instance
     *
     * @return ProcessInstance
     */
    public function getModel()
    {
        $this->_model = $this->_model ?: ProcessInstance::find($this->getId());
        return $this->_model;
    }
}
