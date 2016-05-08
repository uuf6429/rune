<?php

namespace uuf6429\Rune\Util;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class CustomSymfonyExpressionLanguage extends ExpressionLanguage
{
    protected function registerFunctions()
    {
        // disable default Symfony ExpressionLanguage functions
    }

    /**
     * @param callable[string] $functions
     */
    public function setFunctions($functions)
    {
        $this->functions = [];

        foreach ($functions as $name => $call) {
            $this->addFunction(
                new ExpressionFunction(
                    $name,
                    function () use ($name) {
                        return sprintf(
                            '%s(%s)',
                            $name,
                            implode(', ', func_get_args())
                        );
                    },
                    function () use ($call) {
                        return call_user_func_array(
                            $call,
                            array_slice(func_get_args(), 1)
                        );
                    }
                )
            );
        }
    }
}
