<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\TestCase;

class LazyPropertiesTest extends TestCase
{
    public function testLazyLoad()
    {
        $model = $this->getMockForTrait(
            LazyProperties::class,
            [],
            '',
            true,
            true,
            true,
            ['getSomeVar']
        );

        $model->expects($this->once())
            ->method('getSomeVar')
            ->willReturn(42);

        $this->assertEquals(42, $model->someVar);
        $this->assertEquals(42, $model->someVar);
    }

    public function testBrokenLazyLoad()
    {
        $model = $this->getMockForTrait(LazyProperties::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Missing property %s and method %s in class %s.',
                'someVar', 'getSomeVar', get_class($model)
            )
        );

        $model->someVar;
    }
}
