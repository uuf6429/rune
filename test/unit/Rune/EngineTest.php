<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Util\ContextVariable;
use uuf6429\Rune\Util\Evaluator;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContextVariable[]
     */
    protected function getFields()
    {
        return [
            new ContextVariable('COLOR', 'string'),
            new ContextVariable('SIZE', 'string'),
            new ContextVariable('IS_SUPPORTED', 'boolean'),
        ];
    }

    /**
     * @param bool $withBrokenRules
     *
     * @return AbstractRule[]
     */
    protected function getRules($withBrokenRules = false)
    {
        return array_merge(
            [
                new GenericRule('0', 'Empty condition (true by default)', ''),
                new GenericRule('1', 'Blue Products', 'COLOR == "blue"'),
                new GenericRule('2', 'Medium or Big, Green Products', 'SIZE in ["XL","XXL"] and COLOR == "green"'),
                new GenericRule('3', 'Small, Blue Products', 'SIZE in ["S"] and COLOR == "blue"'),
                new GenericRule('4', 'Sale Products', 'not IS_SUPPORTED'),
            ],
            $withBrokenRules
            ? [
                new GenericRule('5', 'Bad Rule - Result Type', 'SIZE'),
                new GenericRule('6', 'Bad Rule - Snytax Error', 'SIZE =  = "hm'),
            ]
            : []
        );
    }

    /**
     * @param array $productFieldValues
     *
     * @return AbstractContext[]
     */
    protected function getContexts($productFieldValues)
    {
        $contexts = [];

        foreach ($productFieldValues as $productName => $fieldValues) {
            $fields = $this->getFields();

            $action = new CallbackAction(
                function (
                    Evaluator $eval,
                    AbstractContext $context,
                    AbstractRule $rule
                ) use (
                    $productName
                ) {
                    $this->matchingRuleIDs[$productName][] = $rule->getID();
                }
            );

            foreach ($fields as $field) {
                if (isset($fieldValues[$field->getName()])) {
                    $field->setValue($fieldValues[$field->getName()]);
                }
            }

            $contexts[] = new DynamicContext($action, $fields);
        }

        if (count($contexts) == 1) {
            // try single-context scenario
            $contexts = $contexts[0];
        }

        return $contexts;
    }

    /**
     * @param bool  $withBadRules
     * @param array $productFieldValues
     * @param array $expectedRuleIDs
     * @param array $expectedErrors
     * @dataProvider sampleValuesDataProvider
     */
    public function testRuleEngine($withBadRules, $productFieldValues, $expectedRuleIDs, $expectedErrors)
    {
        $this->matchingRuleIDs = array_fill_keys(array_keys($productFieldValues), []);
        $contexts = $this->getContexts($productFieldValues);

        $engine = new Engine();
        $engine->execute($contexts, $this->getRules($withBadRules));

        $this->assertEquals($expectedRuleIDs, $this->matchingRuleIDs);

        $errorMesgs = array_map(
            function (\Exception $exception) {
                return $exception->getMessage();
            },
            $engine->getErrors()
        );

        if (empty($expectedErrors)) {
            $this->assertFalse($engine->hasErrors(), 'Engine should not have caused errors');
        } else {
            $this->assertEquals($expectedErrors, $errorMesgs, 'Engine errors were not as expected.');
        }
    }

    /**
     * @return array
     */
    public function sampleValuesDataProvider()
    {
        return [
            'empty condition' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'red',
                        'SIZE' => 'XS',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0'],
                ],
                'expectedErrors' => [],
            ],

            'no matches (except empty condition)' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'red',
                        'SIZE' => 'XS',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0'],
                ],
                'expectedErrors' => [],
            ],

            'simple match' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'IS_SUPPORTED' => true,
                    ],
                    'Product 2' => [
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0'],
                    'Product 2' => ['0', '4'],
                ],
                'expectedErrors' => [],
            ],

            'alternating match' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'green',
                        'SIZE' => 'XXL',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0', '2'],
                ],
                'expectedErrors' => [],
            ],

            'multiple matches - common fields' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'blue',
                        'SIZE' => 'S',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0', '1', '3'],
                ],
                'expectedErrors' => [],
            ],

            'multiple matches - unrelated fields' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'blue',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0', '1', '4'],
                ],
                'expectedErrors' => [],
            ],

            'multiple matches - same field' => [
                'withBadRules' => false,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'red',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                    'Product 2' => [
                        'COLOR' => 'orange',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                    ],
                    'Product 3' => [
                        'COLOR' => 'yellow',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0', '4'],
                    'Product 2' => ['0'],
                    'Product 3' => ['0', '4'],
                ],
                'expectedErrors' => [],
            ],

            'trigger errors' => [
                'withBadRules' => true,
                'productFieldValues' => [
                    'Product 1' => [
                        'COLOR' => 'red',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRuleIDs' => [
                    'Product 1' => ['0'],
                ],
                'expectedErrors' => [
                    'The condition result for rule 5 (Bad Rule - Result Type) '
                    .'should be boolean, not string.',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getFailureModeScenariopProductFieldValues()
    {
        return [
            'Product 1' => [
                'COLOR' => 'red',
            ],
            'Product 2' => [
                'COLOR' => 'green',
            ],
            'Product 3' => [
                'COLOR' => 'blue',
            ],
        ];
    }

    /**
     * @return AbstractRule[]
     */
    protected function getFailureModeScenariopRules()
    {
        return [
            new GenericRule(1, 'Good Rule 1', 'COLOR == "red"'),
            new GenericRule(2, 'Bad Rule', 'COLOR == black'),
            new GenericRule(3, 'Good Rule 2', 'COLOR == "red" or COLOR == "blue"'),
        ];
    }

    /**
     * @param int   $failureMode
     * @param array $expectedRuleIDs
     * @param array $expectedErrors
     * @dataProvider sampleValuesFailureModeDataProvider
     */
    public function testRuleEngineFailureModes($failureMode, $expectedRuleIDs, $expectedErrors)
    {
        $productFieldValues = $this->getFailureModeScenariopProductFieldValues();
        $this->matchingRuleIDs = array_fill_keys(array_keys($productFieldValues), []);
        $contexts = $this->getContexts($productFieldValues);

        $engine = new Engine();
        $engine->execute($contexts, $this->getFailureModeScenariopRules(), $failureMode);

        $this->assertEquals($expectedRuleIDs, $this->matchingRuleIDs);

        $errorMesgs = array_map(
            function (\Exception $exception) {
                return $exception->getMessage();
            },
            $engine->getErrors()
        );

        $this->assertEquals($expectedErrors, $errorMesgs, 'Engine errors were not as expected.');
    }

    /**
     * @return array
     */
    public function sampleValuesFailureModeDataProvider()
    {
        return [
            'test engine failure' => [
                'failureMode' => Engine::ON_ERROR_FAIL_ENGINE,
                'expectedRuleIDs' => [
                    'Product 1' => [1],
                    'Product 2' => [],
                    'Product 3' => [],
                ],
                'expectedErrors' => [
                    'Variable "black" is not valid around position 10.',
                ],
            ],
            'test context failure' => [
                'failureMode' => Engine::ON_ERROR_FAIL_CONTEXT,
                'expectedRuleIDs' => [
                    'Product 1' => [1],
                    'Product 2' => [],
                    'Product 3' => [],
                ],
                'expectedErrors' => [
                    'Variable "black" is not valid around position 10.',
                    'Variable "black" is not valid around position 10.',
                    'Variable "black" is not valid around position 10.',
                ],
            ],
            'test rule failure' => [
                'failureMode' => Engine::ON_ERROR_FAIL_RULE,
                'expectedRuleIDs' => [
                    'Product 1' => [1, 3],
                    'Product 2' => [],
                    'Product 3' => [3],
                ],
                'expectedErrors' => [
                    'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
                    .'while processing rule 2 (Bad Rule) within '.DynamicContext::class
                    .': Variable "black" is not valid around position 10.',
                    'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
                    .'while processing rule 2 (Bad Rule) within '.DynamicContext::class
                    .': Variable "black" is not valid around position 10.',
                    'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
                    .'while processing rule 2 (Bad Rule) within '.DynamicContext::class
                    .': Variable "black" is not valid around position 10.',
                ],
            ],
        ];
    }
}
