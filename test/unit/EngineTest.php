<?php declare(strict_types=1);

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace uuf6429\Rune;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Throwable;
use TypeError;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Engine\ExceptionHandler\CollectExceptions;
use uuf6429\Rune\Engine\FilterAllMatchingRules;
use uuf6429\Rune\Exception\ContextErrorException;
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
                new GenericRule('0', 'Empty condition (true by default)', '', $action),
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
        array $expectedErrors,
        int   $expectedResult
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
        $this->assertSame($expectedResult, $result);
    }

    public static function sampleValuesDataProvider(): iterable
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
                        'ERROR' => static fn ($msg) => trigger_error($msg),
                    ],
                ],
                'expectedRules' => [
                    'Product 1' => [
                        'Empty condition (true by default)',
                    ],
                ],
                'expectedErrors' => [
                    TypeError::class . ' encountered while processing rule 5 '
                    . '(Bad Rule - Result Type) within ' . DynamicContext::class
                    . ': ' . FilterAllMatchingRules::class . '::filterRule()'
                    . ': Return value must be of type bool, string returned',

                    SyntaxError::class . ' encountered while processing rule 6 '
                    . '(Bad Rule - Syntax Error) within ' . DynamicContext::class
                    . ': Unexpected character "=" around position 5 for expression `SIZE =  = "hm`.',

                    RuntimeException::class . ' encountered while processing rule 7 '
                    . '(Bad Rule - Property on a Non-Object) within ' . DynamicContext::class
                    . ': Unable to get property "TEST" of non-object "SIZE".',

                    ContextErrorException::class . ' encountered while processing rule 8 '
                    . '(Bad Rule - Triggers error) within ' . DynamicContext::class
                    . ': some error',
                ],
                'expectedResult' => 1,
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
            'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
            . ': Variable "black" is not valid around position 10 for expression `COLOR == black`.',

            'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
            . ': Variable "black" is not valid around position 10 for expression `COLOR == black`.',

            'Symfony\Component\ExpressionLanguage\SyntaxError encountered '
            . 'while processing rule 2 (Bad Rule) within ' . DynamicContext::class
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
