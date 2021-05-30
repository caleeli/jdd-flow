<?php

namespace JDD\Workflow\Exceptions;

use Exception;

class ProcessNotFoundException extends Exception
{
    public function __construct($bpmn, $processId)
    {
        parent::__construct(trans('jddflow::exceptions.ProcessNotFoundException', \compact('bpmn', 'processId')));
    }
}
