<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

use PHPUnit\Framework\TestCase;
use uuf6429\Rune\TypeInfo\TypeInfoClass;
use uuf6429\Rune\TypeInfo\TypeInfoMember;

class TypeInfoTest extends TestCase
{
    public function testThatToArrayWorks(): void
    {
        $sut = new TypeInfoClass(
            'SomeClass',
            [
                new TypeInfoMember(
                    'prop1',
                    ['string', 'null'],
                    '',
                ),
                new TypeInfoMember(
                    'fun1',
                    ['method'],
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
                'name' => 'SomeClass',
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
                    ],
                ],
                'hint' => 'Class summary',
                'link' => 'https://example.com',
            ],
            $result
        );
    }
}
