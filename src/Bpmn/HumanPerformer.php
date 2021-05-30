<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Bpmn\Contracts\HumanPerformerInterface;
use JDD\Workflow\Bpmn\Contracts\ResourceAssignmentExpressionInterface;
use JDD\Workflow\Events\TaskAssignedEvent;
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
        $ids = $expression($data, $token);
        //$token->getInstance()->getDataStore()->setData((array) $data);
        if (is_array($ids)) {
            $token->setUserId($ids[0]);
            event(new TaskAssignedEvent($token));
        } else {
            $token->setUserId($ids);
            event(new TaskAssignedEvent($token));
        }
    }
}
