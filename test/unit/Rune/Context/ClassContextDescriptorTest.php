<?php

namespace uuf6429\Rune\Context;

class ClassContextDescriptorTest extends \PHPUnit_Framework_TestCase
{
    public function testUnsupportedContext()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Context must be or extends ClassContext.'
        );
        new ClassContextDescriptor(new \stdClass());
    }

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
