<?php

namespace uuf6429\Rune\Engine\ExceptionHandler;

use Throwable;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;

class ThrowExceptions implements ExceptionHandlerInterface
{
    /**
     * @throws Throwable
     */
    public function handle(Throwable $exception): void
    {
        throw $exception;
    }
}
