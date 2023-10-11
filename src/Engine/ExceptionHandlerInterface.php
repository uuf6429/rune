<?php

namespace uuf6429\Rune\Engine;

use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception): void;
}
