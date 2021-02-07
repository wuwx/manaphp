<?php

namespace ManaPHP\Cli;

use ManaPHP\Http\Dispatcher\NotFoundActionException;

/**
 * @property-read \ManaPHP\Cli\ConsoleInterface $console
 */
abstract class Command extends \ManaPHP\Controller
{
    /**
     * @param string $action
     *
     * @return void
     * @throws NotFoundActionException
     *
     */
    public function validateInvokable($action)
    {
        $method = $action . 'Action';

        if (!in_array($method, get_class_methods($this), true)) {
            throw new NotFoundActionException(['`%s::%s` method does not exist', static::class, $method]);
        }
    }
}