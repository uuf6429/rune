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
     * @param string $expectedMessageRegex
     * @dataProvider handlingNonExceptionsDataProvider
     */
    public function testHandlingNonException($value, $expectedMessageRegex)
    {
        $handler = new ExceptionPropagatorHandler();

        $ex = null;

        try {
            $handler->handle($value);
        } catch (\TypeError $ex) {
        } catch (\Exception $ex) {
        }

        $this->assertNotNull($ex);
        $this->assertRegExp('/' . $expectedMessageRegex . '/', $ex->getMessage());
    }

    /**
     * @return array
     */
    public function handlingNonExceptionsDataProvider()
    {
        return [
            'number' => [
                '$value' => 12345,
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()')
                    . ' must be an instance of Exception,'
                    . '(.*?)int(.*?)given',
            ],
            'string' => [
                '$value' => 'Exception',
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()')
                    . ' must be an instance of Exception,'
                    . '(.*?)string given',
            ],
            'object' => [
                '$value' => new \stdClass(),
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()')
                    . ' must be an instance of Exception,'
                    . '(.*?)stdClass given',
            ],
            'array' => [
                '$value' => [],
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()')
                    . ' must be an instance of Exception,'
                    . '(.*?)array given',
            ],
        ];
    }
}
