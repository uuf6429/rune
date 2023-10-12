<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Exception\ContextErrorException;

class SymfonyEvaluator implements EvaluatorInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $variables;
    protected CustomSymfonyExpressionLanguage $exprLang;

    public function __construct()
    {
        $this->exprLang = new CustomSymfonyExpressionLanguage();
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function setFunctions(array $functions): void
    {
        $this->exprLang->setFunctions($functions);
    }

    /**
     * @throws ContextErrorException
     */
    public function compile(string $expression): string
    {
        set_error_handler(static function (int $code, string $message, string $file = 'unknown', int $line = 0, array $context = []): void {
            restore_error_handler();
            throw new ContextErrorException($message, 0, $code, $file, $line, $context);
        });
        $result = $this->exprLang->compile($expression, array_keys($this->variables));
        restore_error_handler();

        return $result;
    }

    /**
     * @throws ContextErrorException
     */
    public function evaluate(string $expression)
    {
        set_error_handler(static function (int $code, string $message, string $file = 'unknown', int $line = 0, array $context = []): void {
            restore_error_handler();
            throw new ContextErrorException($message, 0, $code, $file, $line, $context);
        });
        $result = $this->exprLang->evaluate($expression, $this->variables);
        restore_error_handler();

        return $result;
    }
}
