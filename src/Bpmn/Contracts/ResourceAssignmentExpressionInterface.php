<?php

namespace JDD\Workflow\Bpmn\Contracts;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

interface ResourceAssignmentExpressionInterface extends EntityInterface
{
    const BPMN_PROPERTY_FORMAL_EXPRESSION = 'formalExpression';

    /**
     * Expression used to assign resource(s) to a ResourceRole element
     *
     * @return FormalExpressionInterface
     */
    public function getFormalExpression();
}
