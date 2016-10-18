<?php

namespace uuf6429\Rune\Util;

class LazyPropertiesTest extends \PHPUnit_Framework_TestCase
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

        $this->setExpectedException(
            \RuntimeException::class,
            sprintf(
                'Missing property %s and method %s in class %s.',
                'someVar', 'getSomeVar', get_class($model)
            )
        );

        $model->someVar;
    }
}
