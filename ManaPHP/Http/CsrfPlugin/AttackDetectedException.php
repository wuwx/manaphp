<?php

namespace ManaPHP\Http\CsrfPlugin;

use ManaPHP\Exception;

class AttackDetectedException extends Exception
{
    public function __construct($message = 'Possible CSRF attack detected')
    {
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return 400;
    }
}