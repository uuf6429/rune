<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\TypeInfo\TypeInfoClass;
use uuf6429\Rune\TypeInfo\TypeInfoMethod;
use uuf6429\Rune\TypeInfo\TypeInfoProperty;

class TypeInfoTest extends TestCase
{
    public function testThatToArrayWorks(): void
    {
        $sut = new TypeInfoClass(
            'stdClass',
            [
                new TypeInfoProperty(
                    'prop1',
                    ['string', 'null'],
                    '',
                ),
                new TypeInfoMethod(
                    'fun1',
                    [],
                    null,
                    'https://example.com/2'
                )
            ],
            'Class summary',
            'https://example.com'
        );

        $result = $sut->toArray();

        $this->assertEquals(
            [
                'name' => 'stdClass',
                'members' => [
                    'prop1' => [
                        'name' => 'prop1',
                        'types' => ['string', 'null'],
                        'hint' => null,
                        'link' => null,
                    ],
                    'fun1' => [
                        'name' => 'fun1',
                        'types' => ['method'],
                        'hint' => null,
                        'link' => 'https://example.com/2',
                        'params' => [],
                    ],
                ],
                'hint' => 'Class summary',
                'link' => 'https://example.com',
                'types' => ['class'],
            ],
            $result
        );
    }
}
