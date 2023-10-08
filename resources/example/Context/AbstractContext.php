<?php

namespace uuf6429\Rune\Example\Context;

use uuf6429\Rune\Context\ClassContext;
use uuf6429\Rune\Example\Model\StringUtils;

abstract class AbstractContext extends ClassContext
{
    /**
     * @var \uuf6429\Rune\Example\Model\StringUtils
     */
    public $String;

    public function __construct()
    {
        $this->String = new StringUtils();
    }
}
