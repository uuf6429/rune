<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\TestCase;

class ClassContextDescriptorTest extends TestCase
{
    public function testContextFunctions()
    {
        $mockContext = $this
            ->getMockBuilder(ClassContext::class)
            ->setMethods(['someFunction'])
            ->getMock();
        $mockContext->someProperty = true;

        /* @var ClassContext $mockContext */
        $desc = $mockContext->getContextDescriptor();

        $this->assertArrayHasKey('someFunction', $desc->getFunctions());
        $this->assertArrayHasKey('someProperty', $desc->getVariables());
    }
}
