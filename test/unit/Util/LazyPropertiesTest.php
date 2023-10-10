<?php

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class LazyPropertiesTest extends TestCase
{
    public function testLazyLoad(): void
    {
        /** @var MockObject&stdClass $model */
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
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertEquals(42, $model->someVar);
    }

    public function testBrokenLazyLoad(): void
    {
        /** @var MockObject&stdClass $model */
        $model = $this->getMockForTrait(LazyProperties::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Missing property %s and method %s in class %s.',
                'someVar',
                'getSomeVar',
                get_class($model)
            )
        );

        $this->assertNull($model->someVar);
    }

    public function testReadonlyLazyLoad(): void
    {
        /** @var MockObject&stdClass $model */
        $model = $this->getMockForTrait(LazyProperties::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property %s in class %s is read only and cannot be set.',
                'someVar',
                get_class($model)
            )
        );

        $model->someVar = 123;
    }
}
