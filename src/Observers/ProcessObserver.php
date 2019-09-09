<?php

namespace JDD\Workflow\Observers;

use JDD\Workflow\Events\ProcessUpdated;
use JDD\Workflow\Models\Process;

class ProcessObserver
{
    /**
     * Handle the Process "updated" event.
     *
     * @param  \JDD\Workflow\Models\Process  $user
     */
    public function updated(Process $process)
    {
        app('events')->dispatch(new ProcessUpdated($process));
    }
}
