<?php declare(strict_types=1);

namespace uuf6429\Rune\Engine;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

interface RuleFilterInterface
{
    /**
     * @param iterable<RuleInterface> $rules
     * @return iterable<RuleInterface>
     * @throws Throwable
     */
    public function filterRules(ContextInterface $context, iterable $rules): iterable;
}
