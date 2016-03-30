<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\ContextVariable;
use uuf6429\Rune\Util\Evaluator;
use uuf6429\Rune\Util\TypeAnalyser;
use uuf6429\Rune\Util\TypeInfo;

/**
 * When implementing your own context:
 * - override the constructor for DI but make sure to call parent!
 * - implement getVariableList() to return an array of variables available to context.
 * - implement getValueList() to return an array of (var-name => var-value),
 *   possibly using data injected via constructor.
 * IMPORTANT: Keep into consideration that context can be used for read-only
 * purposes and therefore no data is provided to constructor!
 * In such a case, getValueList() should return an empty array.
 */
abstract class AbstractContext
{
    /**
     * @var AbstractAction|null
     */
    protected $action;

    /**
     * @var ContextVariable[]
     */
    private $variables = [];

    /**
     * @param AbstractAction|null $action
     */
    public function __construct($action = null)
    {
        $this->action = $action;
        $variables = $this->getVariableList();
        $values = $this->getValueList();

        foreach ($variables as $variable) {
            $name = $variable->getName();

            if (isset($values[$name])) {
                $variable->setValue($values[$name]);
            }

            if (isset($this->variables[$name])) {
                throw new \LogicException(sprintf(
                    'Variable named %s already added to list.',
                    $name
                ));
            }

            $this->variables[$name] = $variable;
        }
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValue($name, $default = null)
    {
        $variables = $this->getVariables();

        return isset($variables[$name])
            ? $variables[$name]->getValue() : $default;
    }

    /**
     * @return ContextVariable[]
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param TypeAnalyser|null $analyser
     *
     * @return TypeInfo[string]
     */
    public function getTypeInfo($analyser = null)
    {
        $analyser = $analyser ?: new TypeAnalyser();

        foreach ($this->getVariables() as $variable) {
            foreach ($variable->getTypes() as $type) {
                $analyser->analyse($type);
            }
        }

        return $analyser->getTypes();
    }

    /**
     * @param Evaluator    $eval
     * @param AbstractRule $rule
     */
    public function execute(Evaluator $eval, AbstractRule $rule)
    {
        if ($this->action !== null) {
            $this->action->execute($eval, $this, $rule);
        }
    }

    /**
     * @return ContextVariable[]
     */
    abstract protected function getVariableList();

    /**
     * @return mixed[string]
     */
    abstract protected function getValueList();
}
