<?php

namespace uuf6429\Rune\Exception;

class ExceptionPropagatorHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception|\Throwable
     */
    public function handle(\Exception $exception)
    {
        throw $exception;
    }
}
