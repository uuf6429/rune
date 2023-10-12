<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

interface ContextInterface
{
    public function getContextDescriptor(): AbstractContextDescriptor;
}
