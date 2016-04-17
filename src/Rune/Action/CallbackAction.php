<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\Evaluator;

/**
 * The "quick 'n dirty" action.
 * Note: this action is considerably slower than a direct implementation.
 */
class CallbackAction extends AbstractAction
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * The callback will receive the following arguments:
     * (Evaluator $eval, ContextInterface $context, AbstractRule $rule).
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Evaluator $eval, ContextInterface $context, AbstractRule $rule)
    {
        call_user_func($this->callback, $eval, $context, $rule);
    }
}
