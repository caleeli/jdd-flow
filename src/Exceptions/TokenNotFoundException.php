<?php

namespace JDD\Workflow\Exceptions;

use Exception;

class TokenNotFoundException extends Exception
{
    public function __construct($tokenId)
    {
        parent::__construct(trans('jddflow::exceptions.TokenNotFoundException', ['token_id' => $tokenId]));
    }
}
