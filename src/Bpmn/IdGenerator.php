<?php

namespace JDD\Workflow\Bpmn;

class IdGenerator
{
    /**
     * Returns a unique id sortable in time
     *
     * @return int64
     */
    public static function newInt()
    {
        return \intval(\microtime(true) * 2000000) + \rand(0, 999);
    }
}
