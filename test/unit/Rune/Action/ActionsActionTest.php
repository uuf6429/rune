<?php

namespace uuf6429\Rune\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class ActionsActionTest extends TestCase
{
    public function testExecuteThreeActionsInActionChain(): void
    {
        $actions = [
            $this->getActionMock(),
            $this->getActionMock(),
            $this->getActionMock(),
        ];
        $actionChain = new ActionsAction($actions);
        $actionChain->execute(
            $this->createMock(EvaluatorInterface::class),
            $this->createMock(ContextInterface::class),
            $this->createMock(RuleInterface::class)
        );
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function getActionMock()
    {
        $mock = $this
            ->getMockBuilder(ActionInterface::class)
            ->onlyMethods(['execute'])
            ->getMock();

        $mock->expects($this->once())
            ->method('execute');

        return $mock;
    }
}
