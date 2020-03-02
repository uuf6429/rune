<?php

namespace uuf6429\Rune\Util;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CustomSymfonyExpressionLanguage extends ExpressionLanguage
{
    protected function registerFunctions(): void
    {
        // disable default Symfony ExpressionLanguage functions
    }

    /**
     * @param array<string,callable> $functions
     */
    public function setFunctions(array $functions): void
    {
        $this->functions = [];

        foreach ($functions as $name => $call) {
            $this->addFunction(
                new ExpressionFunction(
                    $name,
                    static function () use ($name) {
                        return sprintf(
                            '%s(%s)',
                            $name,
                            implode(', ', func_get_args())
                        );
                    },
                    static function () use ($call) {
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
