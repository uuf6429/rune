<?php declare(strict_types=1);

namespace uuf6429\Rune\Shop\Context;

use uuf6429\Rune\Context\ClassContext;
use uuf6429\Rune\Shop\Model\StringUtils;

abstract class AbstractContext extends ClassContext
{
    public StringUtils $String;

    public function __construct()
    {
        $this->String = new StringUtils();
    }
}
