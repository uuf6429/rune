<?php

namespace uuf6429\Rune\Context;

/**
 * DynamicContext is very flexible, taking an arbitrary amount of variables and
 * functions. However, this also means that these must be available at all
 * times, even when context type metadata is needed (but not actual values).
 * Usage of this class is discouraged.
 */
class DynamicContext implements ContextInterface
{
    /**
     * @var mixed[string]
     */
    private $variables = [];

    /**
     * @var callable[string]
     */
    private $functions = [];

    /**
     * @var DynamicContextDescriptor
     */
    private $descriptor;

    /**
     * @param mixed[string]    $variables
     * @param callable[string] $functions
     */
    public function __construct($variables = [], $functions = [])
    {
        $this->variables = $variables;
        $this->functions = $functions;
    }

    /**
     * @return DynamicContextDescriptor
     */
    public function getContextDescriptor()
    {
        if (!$this->descriptor) {
            $this->descriptor = new DynamicContextDescriptor($this);
        }

        return $this->descriptor;
    }

    /**
     * @return mixed[string]
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return callable[string]
     */
    public function getFunctions()
    {
        return $this->functions;
    }
}
