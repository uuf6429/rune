<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use uuf6429\Rune\Exception\MissingGetterException;
use uuf6429\Rune\Exception\PropertyNotWritableException;

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

        $this->expectException(MissingGetterException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Neither property %s nor (getter) method %s were defined in class %s.',
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

        $this->expectException(PropertyNotWritableException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Property %s in class %s is not writable',
                'someVar',
                get_class($model)
            )
        );

        $model->someVar = 123;
    }

    public function testResettingWorks(): void
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
        $model->expects($this->exactly(2))
            ->method('getSomeVar')
            ->willReturn(11, 22);

        $result1 = $model->someVar;
        $result2 = $model->someVar;
        unset($model->someVar);
        $result3 = $model->someVar;

        $this->assertEquals(
            [11, 11, 22],
            [$result1, $result2, $result3]
        );
    }

    public function testIssetOnMissingGetter(): void
    {
        /** @var MockObject&stdClass $model */
        $model = $this->getMockForTrait(LazyProperties::class);

        $this->assertFalse(isset($model->someVar));
    }

    public function testIssetOnNullReturningGetter(): void
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
            ->willReturn(null);

        $this->assertFalse(isset($model->someVar));
    }
}
