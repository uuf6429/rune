<?php

namespace uuf6429\Rune\Util;

class ContextRuleException extends \RuntimeException
{
    /**
     * @var ContextRulePair
     */
    private $contextRule;

    /**
     * @param ContextRulePair $contextRule
     * @param string|null     $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        ContextRulePair $contextRule,
        $message = null,
        \Exception $previous = null
    ) {
        if ($message === null) {
            $message = sprintf(
                '%s encountered while processing rule %s (%s) within %s%s',
                (is_object($previous) ? get_class($previous) : 'Error'),
                $contextRule->getRule()->getID(),
                $contextRule->getRule()->getName(),
                get_class($contextRule->getContext()),
                (is_object($previous) ? (': ' . $previous->getMessage()) : '')
            );
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return ContextRulePair
     */
    public function getContextRule()
    {
        return $this->contextRule;
    }
}
