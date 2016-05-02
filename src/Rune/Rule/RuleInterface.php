<?php

namespace uuf6429\Rune\Rule;

interface RuleInterface
{
    /**
     * @return string
     */
    public function getID();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCondition();
}
