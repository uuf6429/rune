<?php

namespace uuf6429\Rune\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class ExceptionPropagatorHandlerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getHandler()
    {
        return new ExceptionPropagatorHandler();
    }

    public function testHandlingExceptions(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rethrown from handler.');

        $this->getHandler()->handle(new Exception('Rethrown from handler.'));
    }
}
