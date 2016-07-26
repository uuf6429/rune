<?php

namespace uuf6429\Rune\Util;

interface EvaluatorInterface
{
    /**
     * @param array<string,mixed> $variables
     */
    public function setVariables($variables);

    /**
     * @param array<string,callable> $functions
     */
    public function setFunctions($functions);

    /**
     * @param string $expression
     *
     * @return string
     */
    public function compile($expression);

    /**
     * @param string $expression
     *
     * @return mixed
     */
    public function evaluate($expression);
}
