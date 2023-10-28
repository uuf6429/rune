<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

interface ArrayableInterface
{
    /**
     * @param null|callable(self, array<string, mixed>): array $serializer
     * @return array<string, mixed>
     */
    public function toArray(?callable $serializer = null): array;
}
