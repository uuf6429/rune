<?php declare(strict_types=1);

namespace uuf6429\Rune\Shop\Model;

class StringUtils
{
    /**
     * Lower-cases some text.
     *
     * @param mixed $text
     *
     * @return string
     */
    public function lower($text)
    {
        return strtolower((string)$text);
    }

    /**
     * Upper-cases some text.
     */
    public function upper(string $text): string
    {
        return strtoupper($text);
    }
}
