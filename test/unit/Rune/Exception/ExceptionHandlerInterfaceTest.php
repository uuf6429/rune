<?php

namespace uuf6429\Rune\Exception;

class ExceptionHandlerInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ExceptionHandlerInterface
     */
    protected function getHandler()
    {
        return $this->getMock(ExceptionHandlerInterface::class);
    }

    /**
     * @param mixed  $value
     * @param string $expectedMessage
     * @dataProvider handlingNonExceptionsDataProvider
     */
    public function testHandlingNonException($value, $expectedMessage)
    {
        $handler = new ExceptionPropagatorHandler();

        try {
            $handler->handle($value);
        } catch (\TypeError $ex) {
            $this->assertRegexp('/' . preg_quote($expectedMessage) . '/', $ex->getMessage());
        } catch (\Exception $ex) {
            $this->assertRegexp('/' . preg_quote($expectedMessage) . '/', $ex->getMessage());
        }
    }

    /**
     * @return array
     */
    public function handlingNonExceptionsDataProvider()
    {
        return [
            'number' => [
                '$value' => 12345,
                '$expectedMessage' => 'Argument 1 passed to ' .
                    ExceptionPropagatorHandler::class .
                    '::handle() must be an instance of Exception, integer given',
            ],
            'string' => [
                '$value' => 'Exception',
                '$expectedMessage' => 'Argument 1 passed to ' .
                    ExceptionPropagatorHandler::class .
                    '::handle() must be an instance of Exception, string given',
            ],
            'object' => [
                '$value' => new \stdClass(),
                '$expectedMessage' => 'Argument 1 passed to ' .
                    ExceptionPropagatorHandler::class .
                    '::handle() must be an instance of Exception, instance of stdClass given',
            ],
            'array' => [
                '$value' => [],
                '$expectedMessage' => 'Argument 1 passed to ' .
                    ExceptionPropagatorHandler::class .
                    '::handle() must be an instance of Exception, array given',
            ],
        ];
    }
}
