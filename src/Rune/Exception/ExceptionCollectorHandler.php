<?php

namespace uuf6429\Rune\Exception;

class ExceptionCollectorHandler implements ExceptionHandlerInterface
{
    protected $exceptions = [];

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    /**
     * @return bool
     */
    public function hasExceptions()
    {
        return (bool) count($this->exceptions);
    }

    /**
     * @return \Exception[]
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function clearExceptions()
    {
        $this->exceptions = [];
    }
}
