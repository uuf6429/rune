<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Engine;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class AbstractConfigurableActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $configDefinitions
     * @param array $expectedConfig
     * @dataProvider configurableActionScenarioDataProvider
     */
    public function testConfigurableActionScenario($configDefinitions, $expectedConfig)
    {
        $engine = new Engine();
        $action = $this->getActionMock($configDefinitions, $expectedConfig);
        $context = new DynamicContext(
            [
                'everything' => 42,
                'numbers' => (object) [
                    'fifty' => 50,
                ],
            ]
        );
        $rules = [new GenericRule(0, 'A Rule', 'true')];
        $engine->execute($context, $rules, $action);
    }

    /**
     * @return array
     */
    public function configurableActionScenarioDataProvider()
    {
        return [
            'no config' => [
                '$configDefinitions' => [],
                '$expectedConfig' => [],
            ],
            'empty expression' => [
                '$configDefinitions' => ['key' => ''],
                '$expectedConfig' => ['key' => null],
            ],
            'simple expressions' => [
                '$configDefinitions' => [
                    '2x7' => '2 * 7',
                    's+n' => '"abc" ~ 123',
                    'cond' => '123 == 456',
                ],
                '$expectedConfig' => [
                    '2x7' => 14,
                    's+n' => 'abc123',
                    'cond' => false,
                ],
            ],
            'expressions using context' => [
                '$configDefinitions' => [
                    'everything times 50' => 'everything * numbers.fifty',
                    '50 shades' => 'numbers.fifty ~ " shades"',
                ],
                '$expectedConfig' => [
                    'everything times 50' => 2100,
                    '50 shades' => '50 shades',
                ],
            ],
            'numeric config keys' => [
                '$configDefinitions' => [
                    0 => '"a"',
                    1 => '"b"',
                    2 => '"c"',
                    6 => '"h"',
                ],
                '$expectedConfig' => [
                    '0' => 'a',
                    '1' => 'b',
                    '2' => 'c',
                    '6' => 'h',
                ],
                '$expectedException' => null,
            ],
            'strange config keys' => [
                '$configDefinitions' => [
                    ' ' => '"a space"',
                    'it\'s "gone"!' => '"some quotes"',
                ],
                '$expectedConfig' => [
                    ' ' => 'a space',
                    'it\'s "gone"!' => 'some quotes',
                ],
                '$expectedException' => null,
            ],
        ];
    }

    /**
     * @param array $configDefinitions
     * @param array $expectedConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function getActionMock($configDefinitions, $expectedConfig)
    {
        $mock = $this
            ->getMockBuilder(AbstractConfigurableAction::class)
            ->setMethods(['getConfigDefinition', 'executeWithConfig'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getConfigDefinition')
            ->willReturn($configDefinitions);

        $mock->expects($this->once())
            ->method('executeWithConfig')
            ->willReturnCallback(
                function ($eval, $context, $rule, $config) use ($expectedConfig) {
                    $this->assertInstanceOf(EvaluatorInterface::class, $eval);
                    $this->assertInstanceOf(ContextInterface::class, $context);
                    $this->assertInstanceOf(RuleInterface::class, $rule);
                    $this->assertSame(
                        $expectedConfig,
                        $config,
                        'Generated config was not as expected.'
                    );
                }
            );

        return $mock;
    }
}
