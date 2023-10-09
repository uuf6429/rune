<?php

namespace uuf6429\Rune\Action;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class CallbackActionTest extends TestCase
{
    public function testCallableIsCalled(): void
    {
        $called = false;

        $callback = function () use (&$called) {
            $called = true;
        };
        $action = new CallbackAction($callback);
        $action->execute(
            $this->createMock(EvaluatorInterface::class),
            $this->createMock(ContextInterface::class),
            $this->createMock(RuleInterface::class)
        );

        $this->assertTrue($called);
    }

    public function testMethodIsCalled(): void
    {
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['exec'])
            ->getMock();
        $mock->expects($this->once())
            ->method('exec');
        $callback = [$mock, 'exec'];

        $action = new CallbackAction($callback);
        $action->execute(
            $this->createMock(EvaluatorInterface::class),
            $this->createMock(ContextInterface::class),
            $this->createMock(RuleInterface::class)
        );
    }
}
