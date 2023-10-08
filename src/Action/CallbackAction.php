<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

/**
 * The "quick 'n dirty" action.
 * Note that this action is considerably slower than a direct implementation.
 */
class CallbackAction implements ActionInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * The callback will receive the following arguments:
     * (EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule).
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule): void
    {
        ($this->callback)($eval, $context, $rule);
    }
}
