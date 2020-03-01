<?php

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\TestCase;

class SymfonyEvaluatorTest extends TestCase
{
    /**
     * @param array  $variables
     * @param array  $functions
     * @param string $expression
     * @param string $expectedCompileResult
     * @param mixed  $expectedExecuteResult
     *
     * @dataProvider evaluatorDataProvider
     */
    public function testEvaluator(
        $variables,
        $functions,
        $expression,
        $expectedCompileResult,
        $expectedExecuteResult
    ): void {
        $evaluator = new SymfonyEvaluator();

        $evaluator->setVariables($variables);
        $evaluator->setFunctions($functions);

        $this->assertEquals($expectedCompileResult, $evaluator->compile($expression));
        $this->assertEquals($expectedExecuteResult, $evaluator->evaluate($expression));
    }

    public function evaluatorDataProvider(): array
    {
        return [
            'simple arithmetic' => [
                '$variables' => ['a' => 2, 'b' => 3.3],
                '$functions' => ['round' => 'round'],
                '$expression' => 'a + round(b)',
                '$expectedCompileResult' => '($a + round($b))',
                '$expectedExecuteResult' => 5,
            ],
            'arithmetic operator order' => [
                '$variables' => ['a' => 2, 'b' => 3, 'c' => 4.8],
                '$functions' => ['round' => 'round'],
                '$expression' => 'a + b * 2 + round(c) * 3',
                '$expectedCompileResult' => '(($a + ($b * 2)) + (round($c) * 3))',
                '$expectedExecuteResult' => 2 + 6 + 15,
            ],
            'string concatenation' => [
                '$variables' => ['name' => 'Joe', 'age' => 12, 'weight' => 39230],
                '$functions' => ['gramsToKilos' => static function ($g) { return ($g / 1000) . 'kg'; }],
                '$expression' => 'name ~ " was " ~ gramsToKilos(weight) ~ " when " ~ age ~ "."',
                '$expectedCompileResult' => '((((($name . " was ") . gramsToKilos($weight)) . " when ") . $age) . ".")',
                '$expectedExecuteResult' => 'Joe was 39.23kg when 12.',
            ],
        ];
    }
}
