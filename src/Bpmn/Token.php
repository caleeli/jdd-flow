<?php

namespace JDD\Workflow\Bpmn;

use JDD\Workflow\Models\ProcessToken;
use ProcessMaker\Nayra\Bpmn\Models\Token as ModelsToken;

class Token extends ModelsToken
{

    /**
     * @var Process
     */
    private $_model;

    /**
     * Get process model of this instance
     *
     * @return Process
     */
    public function getModel()
    {
        $this->_model = $this->_model ?: ProcessToken::find($this->getId());
        return $this->_model;
    }

    public function getImplementation()
    {
        $implementation = $this->getOwnerElement()->getProperty('implementation');
        return $implementation;
    }

    /**
     * Set user owner of the token
     *
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->setProperty('user_id', $userId);
    }

    /**
     * Get user owner of the token
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getProperty('user_id');
    }
}
