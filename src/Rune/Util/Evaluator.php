<?php

namespace uuf6429\Rune\Util;

use Symfony\Component\ExpressionLanguage\Expression;

class Evaluator
{
    /**
     * @var CustomExpressionLanguage
     */
    protected $exprLang;

    /**
     * @var mixed[string]
     */
    protected $variables;

    public function __construct()
    {
        $this->exprLang = new CustomExpressionLanguage();
    }

    /**
     * @param mixed[string] $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    /**
     * @param callable[string] $functions
     */
    public function setFunctions($functions)
    {
        $this->exprLang->setFunctions($functions);
    }

    /**
     * @internal This method should not be called directly.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param array  $errcontext
     *
     * @throws \ErrorException
     */
    public function errorToErrorException($errno, $errstr, $errfile = 'unknown', $errline = 0, $errcontext = [])
    {
        if (error_reporting() & $errno) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
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
        try {
            set_error_handler([$this, 'errorToErrorException']);
            $result = $this->exprLang->compile($expression, array_keys($this->variables));
            restore_error_handler();

            return $result;
        } catch (Exception $ex) {
            restore_error_handler();
            throw $ex;
        }
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
        try {
            set_error_handler([$this, 'errorToErrorException']);
            $result = $this->exprLang->evaluate($expression, $this->variables);
            restore_error_handler();

            return $result;
        } catch (Exception $ex) {
            restore_error_handler();
            throw $ex;
        }
    }
}
