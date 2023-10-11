<?php

namespace uuf6429\Rune\Rule;

use uuf6429\Rune\Action\ActionInterface;

class GenericRule implements RuleInterface
{
    protected string $id;

    protected string $name;

    protected string $condition;
    protected ActionInterface $action;

    public function __construct(string $id, string $name, string $condition, ActionInterface $action)
    {
        $this->id = $id;
        $this->name = $name;
        $this->condition = $condition;
        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}
