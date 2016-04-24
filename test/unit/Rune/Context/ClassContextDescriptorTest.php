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
}
