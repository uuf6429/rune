<?php

namespace uuf6429\Rune\Util;

interface EvaluatorInterface
{
    /**
     * @param array<string,mixed> $variables
     */
    public function setVariables(array $variables): void;

    /**
     * @param array<string,callable> $functions
     */
    public function setFunctions(array $functions): void;

    public function compile(string $expression): string;

    /**
     * @return mixed
     */
    public function evaluate(string $expression);
}
