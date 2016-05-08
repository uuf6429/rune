<?php

namespace uuf6429\Rune\Util;

interface EvaluatorInterface
{
    /**
     * @param mixed[string] $variables
     */
    public function setVariables($variables);

    /**
     * @param callable[string] $functions
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
