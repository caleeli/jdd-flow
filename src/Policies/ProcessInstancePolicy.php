<?php

namespace JDD\Workflow\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use JDD\Workflow\Models\ProcessInstance;

class ProcessInstancePolicy
{
    use HandlesAuthorization;
    use DefaultPolicyTrait;

    protected $modelClass = ProcessInstance::class;
    protected $ownerField = 'user_id';
}
