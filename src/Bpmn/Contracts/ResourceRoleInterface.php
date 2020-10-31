<?php

namespace JDD\Workflow\Bpmn\Contracts;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

interface ResourceRoleInterface extends EntityInterface
{
    const BPMN_PROPERTY_RESOURCE_ASSIGNMENT_EXPRESSION = 'resourceAssignmentExpression';
    const BPMN_PROPERTY_RESOURCE_REF = 'resourceRef';
    const BPMN_PROPERTY_RESOURCE_PARAMETER_BINDINGS = 'resourceParameterBindings';

    /**
     * @return FormalExpressionInterface
     */
    public function getResourceAssignmentExpression();

    /**
     * Evaluate the resource assignment expression
     *
     * @param array $token
     *
     * @return array|int
     */
    public function execute(TokenInterface $token);
}
