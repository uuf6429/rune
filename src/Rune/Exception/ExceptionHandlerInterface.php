<?php

namespace uuf6429\Rune\Exception;

interface ExceptionHandlerInterface
{
    /**
     * @param \Exception $exception
     */
    public function handle(\Exception $exception);
}
