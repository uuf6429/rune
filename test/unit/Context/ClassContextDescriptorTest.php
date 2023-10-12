<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClassContextDescriptorTest extends TestCase
{
    public function testContextFunctions(): void
    {
        $desc = (new SampleContext())->getContextDescriptor();

        $this->assertArrayHasKey('someFunction', $desc->getFunctions());
        $this->assertArrayHasKey('someProperty', $desc->getVariables());
    }
}
