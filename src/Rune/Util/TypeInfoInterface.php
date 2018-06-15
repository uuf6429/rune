<?php

namespace uuf6429\Rune\Util;

interface TypeInfoInterface extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return bool
     */
    public function hasHint();

    /**
     * @return string
     */
    public function getHint();

    /**
     * @return string
     */
    public function getLink();
}
