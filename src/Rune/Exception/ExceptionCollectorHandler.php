<?php

namespace uuf6429\Rune\Exception;

use Throwable;

class ExceptionCollectorHandler implements ExceptionHandlerInterface
{
    protected array $exceptions = [];

    public function handle(Throwable $exception): void
    {
        $this->exceptions[] = $exception;
    }

    public function hasExceptions(): bool
    {
        return (bool)count($this->exceptions);
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function clearExceptions(): void
    {
        $this->exceptions = [];
    }
}
