<?php

namespace uuf6429\Rune\Action;

class ActionsAction implements ActionInterface
{
    /**
     * @var ActionInterface[]
     */
    protected $actions;

    /**
     * @param ActionInterface[] $actions
     */
    public function __construct($actions)
    {
        $this->actions = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($eval, $context, $rule)
    {
        foreach ($this->actions as $action) {
            $action->execute($eval, $context, $rule);
        }
    }
}
