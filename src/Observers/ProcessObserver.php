<?php

namespace JDD\Workflow\Observers;

use JDD\Workflow\Events\ProcessUpdated;
use JDD\Workflow\Models\ProcessInstance;

class ProcessObserver
{
    /**
     * Handle the Process "updated" event.
     *
     * @param  \JDD\Workflow\Models\ProcessInstance  $user
     */
    public function updated(ProcessInstance $process)
    {
        app('events')->dispatch(new ProcessUpdated($process));
    }
}
