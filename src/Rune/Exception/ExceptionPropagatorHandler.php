<?php

namespace uuf6429\Rune\Exception;

use Throwable;

class ExceptionPropagatorHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    public function handle(Throwable $exception): void
    {
        throw $exception;
    }
}
