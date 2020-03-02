<?php

namespace uuf6429\Rune\Context;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClassContextDescriptorTest extends TestCase
{
    public function testUnsupportedContext(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context must be or extends ClassContext.');

        /* @noinspection PhpParamsInspection */
        new ClassContextDescriptor(new stdClass());
    }

    public function testContextFunctions(): void
    {
        $mockContext = $this
            ->getMockBuilder(ClassContext::class)
            ->addMethods(['someFunction'])
            ->getMock();
        $mockContext->someProperty = true;

        /* @var MockObject|ClassContext $mockContext */
        $desc = $mockContext->getContextDescriptor();

        $this->assertArrayHasKey('someFunction', $desc->getFunctions());
        $this->assertArrayHasKey('someProperty', $desc->getVariables());
    }
}
