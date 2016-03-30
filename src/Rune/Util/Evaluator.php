<?php

namespace uuf6429\Rune\Util;

use Symfony\Component\ExpressionLanguage\Expression;

class Evaluator
{
    /**
     * @var ContextVariable[]
     */
    protected $variables;

    /**
     * @var mixed[string]
     */
    protected $values;

    /**
     * @var CustomExpressionLanguage
     */
    protected $exprLang;

    public function __construct()
    {
        $this->exprLang = new CustomExpressionLanguage();
    }

    /**
     * @param ContextVariable[] $variables
     */
    public function setVariables($variables)
    {
        $this->values = [];
        $this->variables = $variables;
        foreach ($variables as $variable) {
            $this->values[$variable->getName()] = $variable->getValue();
        }
    }

    /**
     * Compiles an expression source code.
     *
     * @param Expression|string $expression The expression to compile
     *
     * @return string The compiled PHP source code
     */
    public function compile($expression)
    {
        return $this->exprLang->compile($expression, array_keys($this->values));
    }

    /**
     * Evaluate an expression.
     *
     * @param Expression|string $expression The expression to compile
     *
     * @return string The result of the evaluation of the expression
     */
    public function evaluate($expression)
    {
        return $this->exprLang->evaluate($expression, $this->values);
    }
}
