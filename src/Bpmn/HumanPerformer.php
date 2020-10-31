<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Bpmn\Contracts\HumanPerformerInterface;
use JDD\Workflow\Bpmn\Contracts\ResourceAssignmentExpressionInterface;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

class HumanPerformer implements HumanPerformerInterface
{
    use BaseTrait;

    /**
     * @return ResourceAssignmentExpressionInterface
     */
    public function getResourceAssignmentExpression()
    {
        return $this->getProperty(self::BPMN_PROPERTY_RESOURCE_ASSIGNMENT_EXPRESSION);
    }

    public function execute(TokenInterface $token)
    {
        $expression = $this->getResourceAssignmentExpression()->getFormalExpression();
        $data = $token->getInstance()->getDataStore()->getData();
        $ids = $expression($data);
        if (is_array($ids)) {
            $token->setProperty('user_id', $ids[0]);
        } else {
            $token->setProperty('user_id', $ids);
        }
    }
}
