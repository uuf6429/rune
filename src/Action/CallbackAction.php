<?php declare(strict_types=1);

namespace uuf6429\Rune\Action;

use InvalidArgumentException;
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
     * @param callable|array{0:object,1:string} $callback
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Argument $callback must be a valid callable.');
        }
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
