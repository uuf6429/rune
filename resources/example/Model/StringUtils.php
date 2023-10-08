<?php

namespace uuf6429\Rune\Example\Model;

class StringUtils
{
    /**
     * Lowercases some text.
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
     * Uppercases some text.
     */
    public function upper(string $text): string
    {
        return strtoupper($text);
    }
}
