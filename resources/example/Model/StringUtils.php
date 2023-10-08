<?php

namespace uuf6429\Rune\Example\Model;

class StringUtils
{
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

    /**
     * Uppercases some text.
     *
     * @param string $text
     *
     * @return string
     */
    public function upper($text)
    {
        return strtoupper($text);
    }
}
