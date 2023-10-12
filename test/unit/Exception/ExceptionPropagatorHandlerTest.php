<?php declare(strict_types=1);

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace uuf6429\Rune\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Engine\ExceptionHandler\ThrowExceptions;

class ExceptionPropagatorHandlerTest extends TestCase
{
    public function testHandlingExceptions(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rethrown from handler.');

        (new ThrowExceptions())->handle(new Exception('Rethrown from handler.'));
    }
}
