<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Bpmn\Contracts\ResourceAssignmentExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;

class ResourceAssignmentExpression extends FormalExpression implements ResourceAssignmentExpressionInterface
{
    /**
     * Expression used to assign resource(s) to a ResourceRole element
     *
     * @return FormalExpressionInterface
     */
    public function getFormalExpression()
    {
        return $this->getProperty(self::BPMN_PROPERTY_FORMAL_EXPRESSION);
    }

    /**
     * Evaluate the resource assignment expression
     *
     * @param array $data
     *
     * @return array|int
     */
    public function execute(array $data)
    {
        $this->getFormalExpression()($data);
    }
}
