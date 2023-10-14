<?php declare(strict_types=1);

namespace uuf6429\Rune\Engine\ExceptionHandler;

use Throwable;
use uuf6429\Rune\Engine\ExceptionHandlerInterface;

class CollectExceptions implements ExceptionHandlerInterface
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
