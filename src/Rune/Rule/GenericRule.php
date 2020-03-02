<?php

namespace uuf6429\Rune\Rule;

class GenericRule implements RuleInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $condition;

    /**
     * @param string $id
     * @param string $name
     * @param string $condition
     */
    public function __construct($id, $name, $condition)
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
