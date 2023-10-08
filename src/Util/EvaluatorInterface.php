<?php

namespace uuf6429\Rune\Util;

interface EvaluatorInterface
{
    public function setVariables(array $variables): void;

    public function setFunctions(array $functions): void;

    public function compile(string $expression): string;

    /**
     * @return mixed
     */
    public function evaluate(string $expression);
}
