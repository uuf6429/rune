<?php

namespace uuf6429\Rune\Exception;

class ExceptionPropagatorHandlerTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(\Exception::class, 'Rethrown from handler.');

        $this->getHandler()->handle(new \Exception('Rethrown from handler.'));
    }
}
