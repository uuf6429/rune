<?php

namespace uuf6429\Rune\example\Context;

use uuf6429\Rune\Context\ClassContext;

class ProductContext extends ClassContext
{
    /**
     * @var uuf6429\Rune\example\Model\Product
     */
    public $product;

    /**
     * @param Product|null $product
     */
    public function __construct($product = null)
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ucwords(trim($this->product->colour.' '.$this->product->name));
    }

    /**
     * Lowercases some text.
     *
     * @param string $text
     *
     * @return string
     */
    public function lower($text)
    {
        return strtolower($text);
    }
}
