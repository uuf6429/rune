<?php declare(strict_types=1);

namespace uuf6429\Rune\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Engine\ExceptionHandler\CollectExceptions;

class ExceptionCollectorHandlerTest extends TestCase
{
    public function testCommonOperations(): void
    {
        $handler = new CollectExceptions();

        $this->assertFalse($handler->hasExceptions());
        $this->assertCount(0, $handler->getExceptions());

        $handler->handle(new Exception('Error 1.'));
        $handler->handle(new Exception('Error 2.'));

        $this->assertTrue($handler->hasExceptions());
        $this->assertCount(2, $handler->getExceptions());

        $handler->clearExceptions();

        $this->assertFalse($handler->hasExceptions());
        $this->assertCount(0, $handler->getExceptions());
    }
}
