<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Bpmn\Contracts\PotentialOwnerInterface;
use ProcessMaker\Nayra\Bpmn\BaseTrait;

class PotentialOwner extends HumanPerformer implements PotentialOwnerInterface
{
    use BaseTrait;
}
