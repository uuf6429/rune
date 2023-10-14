<?php declare(strict_types=1);

namespace uuf6429\Rune\Engine;

use Throwable;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;

interface ActionExecutorInterface
{
    /**
     * @param iterable<RuleInterface> $rules
     * @throws Throwable
     */
    public function execute(ContextInterface $context, iterable $rules): int;
}
