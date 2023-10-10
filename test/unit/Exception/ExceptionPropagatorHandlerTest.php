<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace uuf6429\Rune\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class ExceptionPropagatorHandlerTest extends TestCase
{
    public function testHandlingExceptions(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rethrown from handler.');

        (new ExceptionPropagatorHandler())->handle(new Exception('Rethrown from handler.'));
    }
}
