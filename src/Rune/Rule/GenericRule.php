<?php

namespace uuf6429\Rune\Rule;

class GenericRule implements RuleInterface
{
    protected string $id;

    protected string $name;

    protected string $condition;

    public function __construct(string $id, string $name, string $condition)
    {
        $this->id = $id;
        $this->name = $name;
        $this->condition = $condition;
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
}
