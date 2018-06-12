<?php

namespace uuf6429\Rune\Action;

class CallbackActionTest extends \PHPUnit_Framework_TestCase
{
    public function testFunctionIsCalled()
    {
        if (version_compare(PHP_VERSION, '7.2') >= 0) {
            $this->markTestSkipped('"create_function" is not supported in this PHP version and therefore will not be tested.');
        }

        $GLOBALS['called'] = false;

        /* @noinspection PhpDeprecationInspection */
        $callback = create_function('', '$GLOBALS["called"] = true;');
        $action = new CallbackAction($callback);
        $action->execute(null, null, null);

        $this->assertTrue($GLOBALS['called']);
    }

    public function testCallableIsCalled()
    {
        $called = false;

        $callback = function () use (&$called) {
            $called = true;
        };
        $action = new CallbackAction($callback);
        $action->execute(null, null, null);

        $this->assertTrue($called);
    }

    public function testMethodIsCalled()
    {
        $mock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['exec'])
            ->getMock();
        $mock->expects($this->once())
            ->method('exec');
        $callback = [$mock, 'exec'];

        $action = new CallbackAction($callback);
        $action->execute(null, null, null);
    }
}
