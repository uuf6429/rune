<?php

namespace uuf6429\Rune\example\Context;

class ProductContext extends AbstractContext
{
    /**
     * @var \uuf6429\Rune\example\Model\Product
     */
    public $product;

    /**
     * @param null|\uuf6429\Rune\example\Model\Product $product
     */
    public function __construct($product = null)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ucwords(trim($this->product->colour . ' ' . $this->product->name));
    }
}
