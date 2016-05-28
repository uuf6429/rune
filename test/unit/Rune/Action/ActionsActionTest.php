<?php

namespace uuf6429\Rune\Action;

class ActionsActionTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteThreeActionsInActionChain()
    {
        $actions = [
            $this->getActionMock(),
            $this->getActionMock(),
            $this->getActionMock(),
        ];
        $actionChain = new ActionsAction($actions);
        $actionChain->execute(null, null, null);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function getActionMock()
    {
        $mock = $this
            ->getMockBuilder(ActionInterface::class)
            ->setMethods(['execute'])
            ->getMock();

        $mock->expects($this->once())
            ->method('execute');

        return $mock;
    }
}
