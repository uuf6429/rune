<?php declare(strict_types=1);

namespace uuf6429\Rune\Context;

use DateTime;
use PHPUnit\Framework\TestCase;
use uuf6429\Rune\TypeInfo\TypeInfoMethod;
use uuf6429\Rune\TypeInfo\TypeInfoProperty;

class DynamicContextDescriptorTest extends TestCase
{
    /**
     * @dataProvider typeInfoDataProvider
     */
    public function testTypeInfo(array $variables, array $functions, array $expectedVTI, array $expectedFTI, array $expectedDTI): void
    {
        $context = new DynamicContext($variables, $functions);
        $descriptor = $context->getContextDescriptor();

        $this->assertEquals($expectedVTI, $descriptor->getVariableTypeInfo());
        $this->assertEquals($expectedFTI, $descriptor->getFunctionTypeInfo());
        $this->assertEquals($expectedDTI, $descriptor->getDetailedTypeInfo());
    }

    public static function typeInfoDataProvider(): iterable
    {
        return [
            'Simple scalar values test' => [
                '$variables' => [
                    'name' => 'Joe',
                    'age' => 20,
                    'married' => false,
                    'salary' => 600.59,
                    'children' => [],
                ],
                '$functions' => [],
                '$expectedVTI' => [
                    'name' => new TypeInfoProperty('name', ['string']),
                    'age' => new TypeInfoProperty('age', ['integer']),
                    'married' => new TypeInfoProperty('married', ['boolean']),
                    'salary' => new TypeInfoProperty('salary', ['double']),
                    'children' => new TypeInfoProperty('children', ['array']),
                ],
                '$expectedFTI' => [],
                '$expectedDTI' => [],
            ],
            'Functions and methods test' => [
                '$variables' => [],
                '$functions' => [
                    'round' => 'round',
                    'now' => [new DateTime(), 'getTimestamp'],
                ],
                '$expectedVTI' => [],
                '$expectedFTI' => [
                    'round' => new TypeInfoMethod('round', []),
                    'now' => new TypeInfoMethod('now', []),
                ],
                '$expectedDTI' => [],
            ],
        ];
    }
}
