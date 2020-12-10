<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Bpmn\Contracts\PotentialOwnerInterface;
use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

class PotentialOwner extends HumanPerformer implements PotentialOwnerInterface
{
    use BaseTrait;

    public function execute(TokenInterface $token)
    {
        $expression = $this->getResourceAssignmentExpression()->getFormalExpression();
        $data = $token->getInstance()->getDataStore()->getData();
        $ids = $expression($data, $token);
        if (is_array($ids)) {
            $token->setProperty('user_id', $ids[0]);
        } else {
            $token->setProperty('user_id', $ids);
        }
    }
}
