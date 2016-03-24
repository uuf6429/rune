<?php
namespace uuf6429\Rune\Context;

use uuf6429\Rune\Action\AbstractAction;
use uuf6429\Rune\Util\ContextField;

/**
 * Sure, a dynamic context means fields are variable... but it also means no
 * support for type-hinting, both from the source code perspective as well as
 * the frontend expression/calculation UI perspective.
 * Usage of this class is discouraged.
 */
class DynamicContext extends AbstractContext
{
    /**
     * @var ContextField[]
     */
    private $fields;

    /**
     * @param AbstractAction|null $action
     * @param ContextFields[] $fields
     */
    public function __construct($action = null, $fields = [])
    {
        parent::__construct($action);
        $this->fields = $fields;
    }

    /**
     * @return ContextField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    protected function getFieldList()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function getValueList()
    {
        return [];
    }
}
