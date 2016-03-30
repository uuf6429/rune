<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Util\ContextVariable;

/**
 * Sure, a dynamic context sounds very flexible... but it also means no
 * support for type-hinting, both from the source code perspective as well as
 * the frontend expression/calculation UI perspective.
 * Usage of this class is discouraged.
 */
class DynamicContext extends AbstractContext
{
    /**
     * @var ContextVariable[]
     */
    private $variables = [];

    /**
     * @param AbstractAction|null $action
     * @param ContextVariables[]  $variables
     */
    public function __construct($action = null, $variables = [])
    {
        parent::__construct($action);
        foreach ($variables as $variable) {
            /* @var ContextVariable $variable */
            $this->variables[$variable->getName()] = $variable;
        }
    }

    /**
     * @return ContextVariable[]
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * {@inheritdoc}
     */
    protected function getVariableList()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getValueList()
    {
        return [];
    }
}
