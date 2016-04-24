<?php

namespace uuf6429\Rune\Util;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\AbstractRule;

class ContextRulePair
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var AbstractRule
     */
    protected $rule;

    /**
     * @param ContextInterface $context
     * @param AbstractRule     $rule
     */
    public function __construct(ContextInterface $context, AbstractRule $rule)
    {
        $this->context = $context;
        $this->rule = $rule;
    }

    /**
     * @return ContextInterface
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
