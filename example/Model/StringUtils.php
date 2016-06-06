<?php

namespace uuf6429\Rune\example\Model;

use uuf6429\Rune\Model\AbstractModel;

class StringUtils extends AbstractModel
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
