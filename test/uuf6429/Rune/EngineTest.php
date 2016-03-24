<?php
namespace uuf6429\Rune;

use uuf6429\Rune\Action\CallbackAction;
use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Context\DynamicContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Util\ContextField;
use uuf6429\Rune\Util\ContextRuleException;
use uuf6429\Rune\Util\Evaluator;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContextField[]
     */
    protected function getFields()
    {
        return [
            new ContextField('COLOR', 'string'),
            new ContextField('SIZE', 'string'),
            new ContextField('IS_SUPPORTED', 'boolean'),
        ];
    }

    /**
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
     * @param bool $withBadRules
     * @param array $productFieldValues
     * @param array $expectedRuleIDs
     * @param array $expectedErrors
     * @dataProvider sampleValuesDataProvider
     */
    public function testRuleEngine($withBadRules, $productFieldValues, $expectedRuleIDs, $expectedErrors)
    {
        $contexts = [];
        $matchingRuleIDs = array_fill_keys(array_keys($productFieldValues), []);

        foreach ($productFieldValues as $productName => $fieldValues) {
            $fields = $this->getFields();

            $action = new CallbackAction(
                function (
                    Evaluator $eval,
                    AbstractContext $context,
                    AbstractRule $rule
                ) use (
                    $productName,
                    &$matchingRuleIDs
                ) {
                    $matchingRuleIDs[$productName][] = $rule->getID();
                }
            );

            foreach ($fields as $field) {
                if (isset($fieldValues[$field->getName()])) {
                    $field->setValue($fieldValues[$field->getName()]);
                }
            }

            $contexts[] = new DynamicContext($action, $fields);
        }

        $engine = new Engine($contexts, $this->getRules($withBadRules));
        $engine->execute();

        $this->assertEquals($expectedRuleIDs, $matchingRuleIDs);

        $errorMesgs = array_map(
            function (ContextRuleException $exception) {
                return $exception->getMessage();
            },
            $engine->getErrors()
        );

        $this->assertEquals($expectedErrors, $errorMesgs);
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
                    'Product 1' => [ '0' ],
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
                    'Product 1' => [ '0' ],
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
                    'Product 1' => [ '0' ],
                    'Product 2' => [ '0', '4' ],
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
                    'Product 1' => [ '0', '2' ],
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
                    'Product 1' => [ '0', '1', '3' ],
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
                    'Product 1' => [ '0', '1', '4' ],
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
                    'Product 1' => [ '0', '4' ],
                    'Product 2' => [ '0' ],
                    'Product 3' => [ '0', '4' ],
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
                    'Product 1' => [ '0' ],
                ],
                'expectedErrors' => [
                    'RuntimeException encountered while processing rule 5 '
                    . '(Bad Rule - Result Type) within ' . DynamicContext::class
                    . ': The condition result f'
                    . 'or rule 5 (Bad Rule - Result Type) should be boolea'
                    . 'n, not string.',
                    'Symfony\Component\ExpressionLanguage\SyntaxError enco'
                    . 'untered while processing rule 6 (Bad Rule - Snytax '
                    . 'Error) within ' . DynamicContext::class
                    . ': Unexpected character "=" around position 5.',
                ],
            ],
        ];
    }
}
