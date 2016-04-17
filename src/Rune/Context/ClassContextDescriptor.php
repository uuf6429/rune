<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Util\TypeAnalyser;

class ClassContextDescriptor extends AbstractContextDescriptor
{
    /**
     * @var ClassContext
     */
    protected $context;

    /**
     * @param ClassContext $context
     */
    public function __construct($context)
    {
        if (!($context instanceof ClassContext)) {
            throw new \InvalidArgumentException('Context must be or extends ClassContext.');
        }

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array_filter(
            get_class_methods($this->context),
            function ($name) {
                return substr($name, 0, 2) != '__';
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return get_object_vars($this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeInfo()
    {
        $analyser = new TypeAnalyser();
        $class = get_class($this->context);
        $analyser->analyse($class, false);
        $types = $analyser->getTypes();

        return isset($types[$class]) ? $types[$class]->members : [];
    }
}
