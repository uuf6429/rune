<?php
namespace uuf6429\Prune\Rule;

abstract class AbstractRule
{
    /**
     * @return string
     */
    abstract public function getID();

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getCondition();
}
