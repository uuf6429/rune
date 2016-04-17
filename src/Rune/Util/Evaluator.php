<?php

namespace uuf6429\Rune\Util;

use Symfony\Component\ExpressionLanguage\Expression;
use uuf6429\Rune\Context\ContextInterface;

class Evaluator
{
    /**
     * @var CustomExpressionLanguage
     */
    protected $exprLang;

    /**
     * @var ContextInterface
     */
    protected $context;

    public function __construct()
    {
        $this->exprLang = new CustomExpressionLanguage();
    }

    /**
     * @param ContextInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
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
        $this->exprLang->setFunctions(
            $this->context->getContextDescriptor()->getFunctions()
        );

        return $this->exprLang->compile(
            $expression,
            array_keys($this->context->getContextDescriptor()->getVariables())
        );
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
        $this->exprLang->setFunctions(
            $this->context->getContextDescriptor()->getFunctions()
        );

        return $this->exprLang->evaluate(
            $expression,
            $this->context->getContextDescriptor()->getVariables()
        );
    }
}
