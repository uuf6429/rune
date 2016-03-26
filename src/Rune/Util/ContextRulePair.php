<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Rule\AbstractRule;

class ContextRulePair
{
    /**
     * @var AbstractContext
     */
    protected $context;

    /**
     * @var AbstractRule
     */
    protected $rule;

    /**
     * @param AbstractContext $context
     * @param AbstractRule    $rule
     */
    public function __construct(AbstractContext $context, AbstractRule $rule)
    {
        $this->context = $context;
        $this->rule = $rule;
    }

    /**
     * @return AbstractContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return AbstractRule
     */
    public function getRule()
    {
        return $this->rule;
    }
}
