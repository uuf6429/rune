<?php

namespace uuf6429\Rune\Model;

use uuf6429\Rune\TestCase;

/**
 * @deprecated 2.1.0 AbstractModel will be removed in the next major version.
 */
class ModelTest extends TestCase
{
    public function testLazyLoad()
    {
        /** @var AbstractModel $model */
        $model = $this->getMockBuilder(AbstractModel::class)
            ->setMethods(['getSomeVar'])
            ->getMock();

        $model->expects($this->once())
            ->method('getSomeVar')
            ->willReturn(42);

        $this->assertEquals(42, $model->someVar);
        $this->assertEquals(42, $model->someVar);
    }

    public function testBrokenLazyLoad()
    {
        /** @var AbstractModel $model */
        $model = $this->getMockBuilder(AbstractModel::class)
            ->setMethods(null)
            ->getMock();

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
