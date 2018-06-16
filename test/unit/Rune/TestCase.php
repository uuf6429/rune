<?php

namespace uuf6429\Rune;

if (class_exists(\PHPUnit_Framework_TestCase::class)) {
    class TestCase extends \PHPUnit_Framework_TestCase
    {
    }
} elseif (class_exists(\PHPUnit\Framework\TestCase::class)) {
    class TestCase extends \PHPUnit\Framework\TestCase
    {
    }
} else {
    throw new \LogicException('Unknown/unsupported test framework.');
}
