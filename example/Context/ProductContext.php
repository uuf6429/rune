<?php

namespace uuf6429\Rune\example\Context;

use uuf6429\Rune\Context\ClassContext;
use uuf6429\Rune\Example\Model\Product;

class ProductContext extends ClassContext
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @param Product|null $product
     */
    public function __construct($product = null)
    {
        $this->product = $product;
    }

    public function __toString()
    {
        return ucwords(trim($this->product->colour.' '.$this->product->name));
    }
}
