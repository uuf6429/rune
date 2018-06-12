<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Exception\ContextErrorException;

class SymfonyEvaluator implements EvaluatorInterface
{
    /**
     * @var array<string,mixed>
     */
    protected $variables;

    /**
     * @var CustomSymfonyExpressionLanguage
     */
    protected $exprLang;

    public function __construct()
    {
        $this->exprLang = new CustomSymfonyExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function setFunctions($functions)
    {
        $this->exprLang->setFunctions($functions);
    }

    /**
     * @internal this method should not be called directly
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws \ErrorException
     */
    public function errorToErrorException($code, $message, $file = 'unknown', $line = 0, array $context = [])
    {
        restore_error_handler();
        throw new ContextErrorException($message, 0, $code, $file, $line, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function compile($expression)
    {
        set_error_handler([$this, 'errorToErrorException']);
        $result = $this->exprLang->compile($expression, array_keys($this->variables));
        restore_error_handler();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($expression)
    {
        set_error_handler([$this, 'errorToErrorException']);
        $result = $this->exprLang->evaluate($expression, $this->variables);
        restore_error_handler();

        return $result;
    }
}
