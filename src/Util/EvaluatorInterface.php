<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use Throwable;

interface EvaluatorInterface
{
    public function setVariables(array $variables): void;

    public function setFunctions(array $functions): void;

    /**
     * @throws Throwable
     */
    public function compile(string $expression): string;

    /**
     * @return mixed
     * @throws Throwable
     */
    public function evaluate(string $expression);
}
