<?php

namespace uuf6429\Rune\Context;

use DateTime;
use PHPUnit\Framework\TestCase;
use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\TypeInfoClass;
use uuf6429\Rune\Util\TypeInfoMember;

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
                    'name' => new TypeInfoMember('name', ['string']),
                    'age' => new TypeInfoMember('age', ['integer']),
                    'married' => new TypeInfoMember('married', ['boolean']),
                    'salary' => new TypeInfoMember('salary', ['double']),
                    'children' => new TypeInfoMember('children', ['array']),
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
                    'round' => new TypeInfoMember('round', ['callable']),
                    'now' => new TypeInfoMember('now', ['callable']),
                ],
                '$expectedDTI' => [],
            ],
        ];
    }
}
