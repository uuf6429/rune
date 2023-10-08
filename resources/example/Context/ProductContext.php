<?php

namespace uuf6429\Rune\Example\Context;

use Stringable;
use uuf6429\Rune\Example\Model\Product;

class ProductContext extends AbstractContext implements Stringable
{
    public ?Product $product;

    public function __construct(?Product $product = null)
    {
        parent::__construct();

        $this->product = $product;
    }

    public function __toString(): string
    {
        return ucwords(trim($this->product->colour . ' ' . $this->product->name));
    }
}