<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Util\ContextVariable;

/**
 * Sure, a dynamic context means fields are variable... but it also means no
 * support for type-hinting, both from the source code perspective as well as
 * the frontend expression/calculation UI perspective.
 * Usage of this class is discouraged.
 */
class DynamicContext extends AbstractContext
{
    /**
     * @var ContextVariable[]
     */
    private $fields = [];

    /**
     * @param AbstractAction|null $action
     * @param ContextVariables[]  $fields
     */
    public function __construct($action = null, $fields = [])
    {
        parent::__construct($action);
        foreach ($fields as $field) {
            /* @var ContextVariable $field */
            $this->fields[$field->getName()] = $field;
        }
    }

    /**
     * @return ContextVariable[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldList()
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
