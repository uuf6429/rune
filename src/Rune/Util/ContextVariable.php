<?php

namespace uuf6429\Rune\Util;

/**
 * Represents a context variable with a known (preloaded) value.
 * Beware of type safety and try to provide a short description in info.
 * Note: if you would like to "lazy load" data, to decrease DB queries, you need
 * to use objects that implement __get() so you can do load and cache data on
 * demand there.  Make sure that the class has an @property to document this
 * behaviour (this will help type hinting).
 */
class ContextVariable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $types;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string          $name  Name (used in expressions).
     * @param string|string[] $types Data types and/or classes (see: http://goo.gl/IKJhKW).
     * @param string          $info  Documentation for this item (for type hinting).
     * @param string          $link  URL with more info.
     * @param mixed           $value
     */
    public function __construct($name, $types, $info = '', $link = '', $value = null)
    {
        $this->name = $name;
        $this->types = is_string($types) ? [$types] : $types;
        $this->info = $info;
        $this->link = $link;

        if ($value === null) {
            $this->setDefaultValue();
        } else {
            $this->setValue($value);
        }
    }

    public function setDefaultValue()
    {
        $this->value = null;
    }

    /**
     * @var mixed
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
