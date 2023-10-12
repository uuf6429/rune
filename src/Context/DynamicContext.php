<?php declare(strict_types=1);

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
    private array $variables;

    /**
     * @var array<string,callable>
     */
    private array $functions;

    private DynamicContextDescriptor $descriptor;

    /**
     * @param array<string,mixed> $variables
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
        return $this->descriptor ?? ($this->descriptor = new DynamicContextDescriptor($this));
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
