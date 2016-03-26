<?php

namespace uuf6429\Rune\Rule;

class GenericRule extends AbstractRule
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
    public function getID()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
