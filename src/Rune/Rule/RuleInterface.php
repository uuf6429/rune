<?php

namespace uuf6429\Rune\Rule;

interface RuleInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getCondition(): string;
}
