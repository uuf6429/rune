<?php
namespace uuf6429\Prune\Action;

use uuf6429\Prune\Context\AbstractContext;
use uuf6429\Prune\Rule\AbstractRule;
use uuf6429\Prune\Util\Evaluator;

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
     * (Evaluator $eval, AbstractContext $context, AbstractRule $rule)
     * 
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function execute(Evaluator $eval, AbstractContext $context, AbstractRule $rule)
    {
        call_user_func($this->callback, $eval, $context, $rule);
    }
}
