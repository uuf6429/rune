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
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function setFunctions(array $functions): void
    {
        $this->exprLang->setFunctions($functions);
    }

    /**
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @internal this method should not be called directly
     *
     * @throws \ErrorException
     */
    public function errorToErrorException(int $code, string $message, string $file = 'unknown', int $line = 0, array $context = []): void
    {
        restore_error_handler();
        throw new ContextErrorException($message, 0, $code, $file, $line, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $expression): string
    {
        set_error_handler([$this, 'errorToErrorException']);
        $result = $this->exprLang->compile($expression, array_keys($this->variables));
        restore_error_handler();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(string $expression)
    {
        set_error_handler([$this, 'errorToErrorException']);
        $result = $this->exprLang->evaluate($expression, $this->variables);
        restore_error_handler();

        return $result;
    }
}
