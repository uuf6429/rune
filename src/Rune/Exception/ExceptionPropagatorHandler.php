<?php

namespace uuf6429\Rune\Exception;

class ExceptionPropagatorHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $exception)
    {
        throw $exception;
    }
}
