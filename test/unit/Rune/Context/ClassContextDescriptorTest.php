<?php

namespace uuf6429\Rune\Context;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClassContextDescriptorTest extends TestCase
{
    public function testContextFunctions(): void
    {
        /**
         * @var MockObject|ClassContext|stdClass $mockContext
         */
        $mockContext = $this
            ->getMockBuilder(ClassContext::class)
            ->addMethods(['someFunction'])
            ->getMock();
        $mockContext->someProperty = true;

        /* @var ClassContext $mockContext */
        $desc = $mockContext->getContextDescriptor();

        $this->assertArrayHasKey('someFunction', $desc->getFunctions());
        $this->assertArrayHasKey('someProperty', $desc->getVariables());
    }
}
