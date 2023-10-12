<?php declare(strict_types=1);

namespace uuf6429\Rune\Example\Context;

use Stringable;
use uuf6429\Rune\Example\Model\Product;

class ProductContext extends AbstractContext implements Stringable
{
    /**
     * @todo This should be non-nullable
     */
    public ?Product $product;

    /**
     * @todo $product should be non-nullable
     */
    public function __construct(?Product $product = null)
    {
        parent::__construct();

        $this->product = $product;
    }

    public function __toString(): string
    {
        return $this->product ? ucwords(trim($this->product->colour . ' ' . $this->product->name)) : 'empty context';
    }
}
