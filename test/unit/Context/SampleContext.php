<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

class SampleContext extends ClassContext
{
    public string $someProperty = 'some value 1';

    public function someFunction(): string
    {
        return 'some value 2';
    }
}
