<?php

namespace uuf6429\Rune;

use PHPUnit\Framework\TestCase;

class ReadmeTest extends TestCase
{
    public function testThatReadmeExampleWorks(): void
    {
        $markdown = file_get_contents(__DIR__ . '/../../README.md');
        if (!$markdown) {
            $this->fail('Could read README.md file');
        }
        if (!preg_match('/```php(.+?)```/s', $markdown, $matches)) {
            $this->fail('Could not find example PHP code in README.md file');
        }
        $examplePhp = $matches[1];

        $this->expectOutputString("Rule 1 triggered for Red Bricks\nRule 3 triggered for Green Soft Socks\n");

        eval($examplePhp);
    }
}
