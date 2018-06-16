<?php

namespace uuf6429\Rune\Exception;

class ExceptionPropagatorHandlerTest extends \PHPUnit\Framework\TestCase
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
