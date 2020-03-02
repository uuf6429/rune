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

    /**
     * @param string $expression
     *
     * @return string
     */
    public function compile(string $expression): string;

    /**
     * @param string $expression
     *
     * @return mixed
     */
    public function evaluate(string $expression);
}
