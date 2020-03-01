<?php

namespace uuf6429\Rune\Exception;

use Exception;

class ExceptionCollectorHandlerTest extends ExceptionHandlerInterfaceTest
{
    /**
     * {@inheritdoc}
     */
    protected function getHandler()
    {
        return new ExceptionCollectorHandler();
    }

    public function testCommonOperations(): void
    {
        $handler = $this->getHandler();

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
