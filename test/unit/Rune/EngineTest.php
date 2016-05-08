<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\ContextErrorException;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use RuntimeException;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $withBrokenRules
     *
     * @return RuleInterface[]
     */
    protected function getRules($withBrokenRules = false)
    {
        return array_merge(
            [
                new GenericRule('0', 'Empty condition (true by default)', ''),
                new GenericRule('1', 'Blue Products', 'COLOR == "blue"'),
                new GenericRule('2', 'Medium or Big, Green Products', 'SIZE in ["XL","XXL"] and COLOR == "green"'),
                new GenericRule('3', 'Small, Blue Products', 'SIZE in ["S"] and COLOR == "blue"'),
                new GenericRule('4', 'Unsupported Products', 'not IS_SUPPORTED'),
            ],
            $withBrokenRules
            ? [
                new GenericRule('5', 'Bad Rule - Result Type', 'SIZE'),
                new GenericRule('6', 'Bad Rule - Syntax Error', 'SIZE =  = "hm'),
                new GenericRule('7', 'Bad Rule - Property on a Non-Object', 'SIZE.TEST == 12'),
                new GenericRule('8', 'Bad Rule - Divide by Zero', '50 / 0'),
            ]
            : []
        );
    }

    /**
     * @param string $productName
     *
     * @return CallbackAction
     */
    protected function getAction($productName)
    {
        return  new CallbackAction(
                /**
                 * @param EvaluatorInterface $eval,
                 * @param ContextInterface   $context,
                 * @param RuleInterface      $rule
                 */
                function ($eval, $context, $rule) use ($productName) {
                    $this->matchingRules[$productName][] = $rule->getName();
                }
            );
    }

    /**
     * @param mixed[string] $productValues
     *
     * @return DynamicContext
     */
    protected function getContext($productValues)
    {
        return new DynamicContext($productValues);
    }

    /**
     * @param bool  $withBadRules
     * @param array $productData
     * @param array $expectedRules
     * @param array $expectedErrors
     * 
     * @dataProvider sampleValuesDataProvider
     */
    public function testRuleEngine(
        $withBadRules,
        $productData,
        $expectedRules,
        $expectedErrors,
        $expectedResult
    ) {
        $this->matchingRules = array_fill_keys(array_keys($productData), []);

        $result = 0;
        $errors = [];
        $engine = new Engine();

        foreach ($productData as $productName => $productValues) {
            $result += $engine->execute(
                $this->getContext($productValues),
                $this->getRules($withBadRules),
                $this->getAction($productName),
                $withBadRules ? Engine::ON_ERROR_FAIL_RULE : Engine::ON_ERROR_FAIL_CONTEXT
            );
            $errors += $engine->getErrors();
        }

        $errorMesgs = array_map(
            function (\Exception $exception) {
                return $exception->getMessage();
            },
            $errors
        );

        if (empty($expectedErrors)) {
            $this->assertEquals([], $errorMesgs, 'Engine should not have caused errors');
        } else {
            $this->assertEquals($expectedErrors, $errorMesgs, 'Engine errors were not as expected.');
        }

        $this->assertEquals($expectedRules, $this->matchingRules);

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function sampleValuesDataProvider()
    {
        return [
            'empty condition' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'red',
                        'SIZE' => 'XS',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 1,
            ],

            'no matches (except empty condition)' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'red',
                        'SIZE' => 'XS',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 1,
            ],

            'simple match' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'orange',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                    ],
                    'Product 2' => [
                        'NAME' => 'Product 2',
                        'COLOR' => 'maroon',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                    ],
                    'Product 2' => [
                        'Empty condition (true by default)',
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 3,
            ],

            'alternating match' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'green',
                        'SIZE' => 'XXL',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                        'Medium or Big, Green Products',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 2,
            ],

            'multiple matches - common variables' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'blue',
                        'SIZE' => 'S',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                        'Blue Products',
                        'Small, Blue Products',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 3,
            ],

            'multiple matches - unrelated variables' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'blue',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                        'Blue Products',
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 3,
            ],

            'multiple matches - same variable' => [
                'withBadRules' => false,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'red',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                    'Product 2' => [
                        'NAME' => 'Product 2',
                        'COLOR' => 'orange',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                    ],
                    'Product 3' => [
                        'NAME' => 'Product 3',
                        'COLOR' => 'yellow',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => false,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                        'Unsupported Products',
                    ],
                    'Product 2' => [
                        'Empty condition (true by default)',
                    ],
                    'Product 3' => [
                        'Empty condition (true by default)',
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
                'expectedResult' => 5,
            ],

            'trigger errors' => [
                'withBadRules' => true,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'red',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                    ],
                ],
                'expectedErrors' => [
                    RuntimeException::class.' encountered while processing rule 5 '
                    .'(Bad Rule - Result Type) within '.DynamicContext::class
                    .': The condition result for rule 5 (Bad Rule - Result '
                    .'Type) should be boolean, not string.',

                    SyntaxError::class.' encountered while processing rule 6 '
                    .'(Bad Rule - Syntax Error) within '.DynamicContext::class
                    .': Unexpected character "=" around position 5.',

                    RuntimeException::class.' encountered while processing rule 7 '
                    .'(Bad Rule - Property on a Non-Object) within '.DynamicContext::class
                    .': Unable to get a property on a non-object.',

                    ContextErrorException::class.' encountered while processing rule 8 '
                    .'(Bad Rule - Divide by Zero) within '.DynamicContext::class
                    .': Division by zero',
                ],
                'expectedResult' => 1,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getFailureModeScenariopProductData()
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
     * @return RuleInterface[]
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
     * @param array $expectedRules
     * @param array $expectedErrors
     * 
     * @dataProvider sampleValuesFailureModeDataProvider
     */
    public function testRuleEngineFailureModes($failureMode, $expectedRules, $expectedErrors)
    {
        $this->markTestSkipped('Failure mode needs to be redesigned.');

        $productData = $this->getFailureModeScenariopProductData();
        $this->matchingRules = array_fill_keys(array_keys($productData), []);

        $errors = [];
        $engine = new Engine();

        foreach ($productData as $productName => $productValues) {
            $engine->execute(
                $this->getContext($productValues),
                $this->getFailureModeScenariopRules(),
                $this->getAction($productName),
                $failureMode
            );
            $errors += $engine->getErrors();
        }

        $this->assertEquals($expectedRules, $this->matchingRules);

        $errorMesgs = array_map(
            function (\Exception $exception) {
                return $exception->getMessage();
            },
            $errors
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
                'expectedRules' => [
                    'Product 1' => [],
                    'Product 2' => [],
                    'Product 3' => [],
                ],
                'expectedErrors' => [
                    'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
                    .'while processing rule 2 (Bad Rule) within '.DynamicContext::class
                    .': Variable "black" is not valid around position 10.',
                ],
            ],
            'test context failure' => [
                'failureMode' => Engine::ON_ERROR_FAIL_CONTEXT,
                'expectedRules' => [
                    'Product 1' => [1],
                    'Product 2' => [],
                    'Product 3' => [],
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
            'test rule failure' => [
                'failureMode' => Engine::ON_ERROR_FAIL_RULE,
                'expectedRules' => [
                    'Product 1' => ['Blue Products', 'Small, Blue Products'],
                    'Product 2' => [],
                    'Product 3' => ['Small, Blue Products'],
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
