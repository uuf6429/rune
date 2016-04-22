<?php

namespace uuf6429\Rune\Model;

class ModelTest extends \PHPUnit_Framework_TestCase
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
