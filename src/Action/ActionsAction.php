<?php declare(strict_types=1);

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

class ActionsAction implements ActionInterface
{
    /**
     * @var ActionInterface[]
     */
    protected array $actions;

    /**
     * @param ActionInterface[] $actions
     */
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule): void
    {
        foreach ($this->actions as $action) {
            $action->execute($eval, $context, $rule);
        }
    }
}
