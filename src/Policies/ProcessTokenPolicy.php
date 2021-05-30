<?php

namespace JDD\Workflow\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use JDD\Workflow\Models\ProcessToken;

class ProcessTokenPolicy
{
    use HandlesAuthorization;
    use DefaultPolicyTrait;

    protected $modelClass = ProcessToken::class;
    protected $ownerField = 'user_id';
}
