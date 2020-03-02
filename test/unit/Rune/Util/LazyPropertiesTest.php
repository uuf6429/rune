<?php

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Exception\InvalidLazyPropertyException;

class LazyPropertiesTest extends TestCase
{
    /**
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testLazyLoad(): void
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

    /**
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testBrokenLazyLoad(): void
    {
        $model = $this->getMockForTrait(LazyProperties::class);

        $this->expectException(InvalidLazyPropertyException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Missing property %s and method %s in class %s.',
                'someVar', 'getSomeVar', get_class($model)
            )
        );

        $model->someVar;
    }
}
