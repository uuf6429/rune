<?php

namespace uuf6429\Rune\Exception;

use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception);
}
