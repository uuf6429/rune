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
     * @var array<string,mixed>
     */
    private $variables;

    /**
     * @var array<string,callable>
     */
    private $functions;

    /**
     * @var DynamicContextDescriptor
     */
    private $descriptor;

    /**
     * @param array<string,mixed>    $variables
     * @param array<string,callable> $functions
     */
    public function __construct(array $variables = [], array $functions = [])
    {
        $this->variables = $variables;
        $this->functions = $functions;
    }

    /**
     * @return DynamicContextDescriptor
     */
    public function getContextDescriptor(): AbstractContextDescriptor
    {
        if (!$this->descriptor) {
            $this->descriptor = new DynamicContextDescriptor($this);
        }

        return $this->descriptor;
    }

    /**
     * @return array<string,mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return array<string,callable>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }
}
