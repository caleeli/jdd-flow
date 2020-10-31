<?php

namespace JDD\Workflow\Bpmn;

use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\RepositoryTrait;
use ProcessMaker\Test\Models\CallActivity;
use ProcessMaker\Test\Models\TestOneClassWithEmptyConstructor;
use ProcessMaker\Test\Models\TestTwoClassWithArgumentsConstructor;

/**
 * Repository
 *
 * @package ProcessMaker\Test\Models
 */
class Repository implements RepositoryInterface
{
    use RepositoryTrait;

    /**
     * Create instance of FormalExpression.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function createFormalExpression()
    {
        return new FormalExpression();
    }

    /**
     * Create instance of CallActivity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface
     */
    public function createCallActivity()
    {
        return new CallActivity();
    }

    /**
     * Create instance of ScriptTask.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface
     */
    public function createScriptTask()
    {
        return new ScriptTask();
    }

    /**
     * Create a execution instance repository.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstanceRepository
     */
    public function createExecutionInstanceRepository()
    {
        return new ExecutionInstanceRepository();
    }

    /**
     * Create a test class
     *
     * @return TestOneClassWithEmptyConstructor
     */
    public function createTestOne()
    {
        return new TestOneClassWithEmptyConstructor();
    }

    /**
     * Create a test class with parameters
     *
     * @param mixed $field1
     * @param mixed $field2
     *
     * @return TestTwoClassWithArgumentsConstructor
     */
    public function createTestTwo($field1, $field2)
    {
        return new TestTwoClassWithArgumentsConstructor($field1, $field2);
    }

    /**
     * Creates a TokenRepository
     *
     * @return \ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface
     */
    public function getTokenRepository()
    {
        if ($this->tokenRepo === null) {
            $this->tokenRepo = new TokenRepository();
        }
        return $this->tokenRepo;
    }

    public function createPotentialOwner()
    {
        return new PotentialOwner();
    }

    public function createResourceAssignmentExpression()
    {
        return new ResourceAssignmentExpression();
    }

    public function createHumanPerformer()
    {
        return new HumanPerformer();
    }
}
