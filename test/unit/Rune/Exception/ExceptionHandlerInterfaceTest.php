<?php

namespace uuf6429\Rune\Exception;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

class ExceptionHandlerInterfaceTest extends TestCase
{
    /**
     * @return MockObject|ExceptionHandlerInterface
     */
    protected function getHandler()
    {
        return $this->getMockBuilder(ExceptionHandlerInterface::class)
            ->getMock();
    }

    /**
     * @param mixed  $value
     * @param string $expectedMessageRegex
     * @dataProvider handlingNonExceptionsDataProvider
     */
    public function testHandlingNonException($value, $expectedMessageRegex): void
    {
        $handler = new ExceptionPropagatorHandler();

        $ex = null;

        try {
            $handler->handle($value);
        } catch (Throwable $ex) {
        }

        $this->assertNotNull($ex);
        $this->assertRegExp('/' . $expectedMessageRegex . '/', $ex->getMessage());
    }

    public function handlingNonExceptionsDataProvider(): array
    {
        return [
            'number' => [
                '$value' => 12345,
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()', '/')
                    . ' must implement interface Throwable,'
                    . '(.*?)int(.*?)given(.*?)',
            ],
            'string' => [
                '$value' => 'Exception',
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()', '/')
                    . ' must implement interface Throwable,'
                    . '(.*?)string given(.*?)',
            ],
            'object' => [
                '$value' => new stdClass(),
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()', '/')
                    . ' must implement interface Throwable,'
                    . '(.*?)stdClass given(.*?)',
            ],
            'array' => [
                '$value' => [],
                '$expectedMessage' => 'Argument 1 passed to '
                    . preg_quote(ExceptionPropagatorHandler::class . '::handle()', '/')
                    . ' must implement interface Throwable,'
                    . '(.*?)array given(.*?)',
            ],
        ];
    }
}
