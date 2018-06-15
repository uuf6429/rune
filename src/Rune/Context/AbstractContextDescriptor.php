<?php

namespace uuf6429\Rune\Context;

/**
 * @deprecated Will be removed in the future. Use ContextDescriptorInterface instead.
 */
abstract class AbstractContextDescriptor implements ContextDescriptorInterface
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param ContextInterface $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }
}
