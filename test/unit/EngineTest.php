<?php declare(strict_types=1);

namespace uuf6429\Rune;

use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Engine\ExceptionHandler\CollectExceptions;
use uuf6429\Rune\Engine\FilterAllMatchingRules;
use uuf6429\Rune\Exception\InvalidExpressionException;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class EngineTest extends TestCase
{
    private array $matchingRules;

    /**
     * @return RuleInterface[]
     */
    protected function getRules(bool $withBrokenRules, ActionInterface $action): array
    {
        return array_merge(
            [
                new GenericRule('1', 'Blue Products', 'COLOR == "blue"', $action),
                new GenericRule('2', 'Medium or Big, Green Products', 'SIZE in ["XL","XXL"] and COLOR == "green"', $action),
                new GenericRule('3', 'Small, Blue Products', 'SIZE in ["S"] and COLOR == "blue"', $action),
                new GenericRule('4', 'Unsupported Products', 'not IS_SUPPORTED', $action),
            ],
            $withBrokenRules ? [
                new GenericRule('5', 'Bad Rule - Result Type', 'SIZE', $action),
                new GenericRule('6', 'Bad Rule - Syntax Error', 'SIZE =  = "hm', $action),
                new GenericRule('7', 'Bad Rule - Property on a Non-Object', 'SIZE.TEST == 12', $action),
                new GenericRule('8', 'Bad Rule - Triggers error', 'ERROR("some error")', $action),
                new GenericRule('9', 'Bad Rule - Empty condition', '', $action),
            ] : []
        );
    }

    protected function getAction(string $productName): CallbackAction
    {
        return new CallbackAction(
            function (EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule) use ($productName) {
                $this->matchingRules[$productName][] = $rule->getName();
            }
        );
    }

    /**
     * @param array<string,mixed> $productValues
     */
    protected function getContext(array $productValues): DynamicContext
    {
        return new DynamicContext(
            array_filter($productValues, static fn ($value) => !is_callable($value)),
            array_filter($productValues, static fn ($value) => is_callable($value)),
        );
    }

    /**
     * @dataProvider sampleValuesDataProvider
     */
    public function testRuleEngine(
        bool  $withBadRules,
        array $productData,
        array $expectedRules,
        array $expectedErrors
    ): void {
        $this->matchingRules = array_fill_keys(array_keys($productData), []);

        $result = 0;
        $exceptionHandler = new CollectExceptions();
        $engine = new Engine(null, null, $exceptionHandler);

        foreach ($productData as $productName => $productValues) {
            $result += $engine->execute(
                $this->getContext($productValues),
                $this->getRules($withBadRules, $this->getAction($productName))
            );
        }

        $errorMsgs = array_map(
            static function (Throwable $exception) {
                return $exception->getMessage();
            },
            $exceptionHandler->getExceptions()
        );

        if (empty($expectedErrors)) {
            $this->assertEquals([], $errorMsgs, 'Engine should not have caused errors');
        } else {
            $this->assertEquals($expectedErrors, $errorMsgs, 'Engine errors were not as expected.');
        }
        $this->assertEquals($expectedRules, $this->matchingRules);
        $this->assertSame(array_sum(array_map('count', $expectedRules)), $result);
    }

    public static function sampleValuesDataProvider(): iterable
    {
        return [
            'no matches' => [
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
                    'Product 1' => [],
                ],
                'expectedErrors' => [],
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
                    'Product 1' => [],
                    'Product 2' => [
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
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
                        'Medium or Big, Green Products',
                    ],
                ],
                'expectedErrors' => [],
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
                        'Blue Products',
                        'Small, Blue Products',
                    ],
                ],
                'expectedErrors' => [],
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
                        'Blue Products',
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
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
                        'Unsupported Products',
                    ],
                    'Product 2' => [
                    ],
                    'Product 3' => [
                        'Unsupported Products',
                    ],
                ],
                'expectedErrors' => [],
            ],

            'trigger errors' => [
                'withBadRules' => true,
                'productData' => [
                    'Product 1' => [
                        'NAME' => 'Product 1',
                        'COLOR' => 'red',
                        'SIZE' => 'M',
                        'IS_SUPPORTED' => true,
                        'ERROR' => static fn ($msg) => trigger_error($msg),
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                    ],
                ],
                'expectedErrors' => [
                    [
                        8 => InvalidExpressionException::class . ' encountered while processing rule 5 '
                            . '(Bad Rule - Result Type) within ' . DynamicContext::class
                            . ': Expression `SIZE` could not be evaluated'
                            . ': ' . FilterAllMatchingRules::class . '::filterRule()'
                            . ': Return value must be of type bool, string returned',
                        7 => InvalidExpressionException::class . ' encountered while processing rule 5 '
                            . '(Bad Rule - Result Type) within ' . DynamicContext::class
                            . ': Expression `SIZE` could not be evaluated'
                            . ': Return value of ' . FilterAllMatchingRules::class . '::filterRule()'
                            . ' must be of the type bool, string returned',
                    ][PHP_MAJOR_VERSION],

                    InvalidExpressionException::class . ' encountered while processing rule 6 '
                    . '(Bad Rule - Syntax Error) within ' . DynamicContext::class
                    . ': Expression `SIZE =  = "hm` could not be evaluated'
                    . ': Unexpected character "=" around position 5 for expression `SIZE =  = "hm`.',

                    InvalidExpressionException::class . ' encountered while processing rule 7 '
                    . '(Bad Rule - Property on a Non-Object) within ' . DynamicContext::class
                    . ': Expression `SIZE.TEST == 12` could not be evaluated'
                    . ': Unable to get property "TEST" of non-object "SIZE".',

                    InvalidExpressionException::class . ' encountered while processing rule 8 '
                    . '(Bad Rule - Triggers error) within ' . DynamicContext::class
                    . ': Expression `ERROR("some error")` could not be evaluated'
                    . ': some error',

                    InvalidExpressionException::class . ' encountered '
                    . 'while processing rule 9 (Bad Rule - Empty condition) within ' . DynamicContext::class
                    . ': Condition expression of 9 must not be empty.',
                ],
            ],
        ];
    }

    public function testRuleEngineCollectingExceptionHandler(): void
    {
        $productData = [
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
        $expectedRules = [
            'Product 1' => ['Good Rule 1', 'Good Rule 3'],
            'Product 2' => [],
            'Product 3' => ['Good Rule 3'],
        ];
        $expectedExceptions = [
            InvalidExpressionException::class . ' encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
            . ': Expression `COLOR == black` could not be evaluated'
            . ': Variable "black" is not valid around position 10 for expression `COLOR == black`.',

            InvalidExpressionException::class . ' encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
            . ': Expression `COLOR == black` could not be evaluated'
            . ': Variable "black" is not valid around position 10 for expression `COLOR == black`.',

            InvalidExpressionException::class . ' encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
            . ': Expression `COLOR == black` could not be evaluated'
            . ': Variable "black" is not valid around position 10 for expression `COLOR == black`.',
        ];

        $this->matchingRules = array_fill_keys(array_keys($productData), []);

        $exceptionHandler = new CollectExceptions();
        $engine = new Engine(null, null, $exceptionHandler);

        foreach ($productData as $productName => $productValues) {
            $action = $this->getAction($productName);

            $rules = [
                new GenericRule('1', 'Good Rule 1', 'COLOR == "red"', $action),
                new GenericRule('2', 'Bad Rule', 'COLOR == black', $action),
                new GenericRule('3', 'Good Rule 3', 'COLOR == "red" or COLOR == "blue"', $action),
            ];

            $engine->execute($this->getContext($productValues), $rules);
        }

        $this->assertEquals($expectedRules, $this->matchingRules);

        $errorMegs = array_map(
            static fn (Throwable $exception) => $exception->getMessage(),
            $exceptionHandler->getExceptions()
        );

        $this->assertEquals($expectedExceptions, $errorMegs, 'Engine exceptions were not as expected.');
    }

    public function testRuleEngineSometimesFaultyAction(): void
    {
        $productData = [
            'Product 1' => [],
            'Product 2' => [],
            'Product 3' => [],
        ];
        $expectedRules = [
            'Product 1' => ['Always triggered'],
            'Product 2' => [],
            'Product 3' => ['Always triggered'],
        ];
        $expectedExceptions = [
            'LogicException encountered while executing action ' . CallbackAction::class
            . ' for rule 1 (Always triggered) within ' . DynamicContext::class
            . ': Exception thrown for Product 2.',
        ];

        $matchingRules = array_fill_keys(array_keys($productData), []);

        $exceptionHandler = new CollectExceptions();
        $engine = new Engine(null, null, $exceptionHandler);

        foreach ($productData as $productName => $productValues) {
            $action = new CallbackAction(
                function (EvaluatorInterface $eval, DynamicContext $context, RuleInterface $rule) use ($productName, &$matchingRules) {
                    if ($productName === 'Product 2') {
                        throw new LogicException("Exception thrown for $productName.");
                    }

                    $matchingRules[$productName][] = $rule->getName();
                }
            );

            $rules = [
                new GenericRule('1', 'Always triggered', 'true', $action),
            ];

            $engine->execute($this->getContext($productValues), $rules);
        }

        $this->assertEquals($expectedRules, $matchingRules);

        $errorMsgs = array_map(
            static fn (Throwable $exception) => $exception->getMessage(),
            $exceptionHandler->getExceptions()
        );

        $this->assertEquals($expectedExceptions, $errorMsgs, 'Engine exceptions were not as expected.');
    }
}
