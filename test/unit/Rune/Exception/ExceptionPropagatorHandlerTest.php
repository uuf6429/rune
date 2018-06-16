<?php

namespace uuf6429\Rune\Exception;

use uuf6429\Rune\TestCase;

class ExceptionPropagatorHandlerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getHandler()
    {
        return new ExceptionPropagatorHandler();
    }

    public function testHandlingExceptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Rethrown from handler.');

        $this->getHandler()->handle(new \Exception('Rethrown from handler.'));
    }
}
