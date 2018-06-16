<?php

namespace uuf6429\Rune\Context;

use uuf6429\Rune\Rule\GenericRule;
use uuf6429\Rune\TestCase;
use uuf6429\Rune\Util\TypeInfoClass;
use uuf6429\Rune\Util\TypeInfoMember;

class DynamicContextDescriptorTest extends TestCase
{
    public function testUnsupportedContext()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Context must be or extends DynamicContext.');

        /* @noinspection PhpParamsInspection */
        new DynamicContextDescriptor(new \stdClass());
    }

    /**
     * @param array $variables
     * @param array $functions
     * @param array $expectedVTI
     * @param array $expectedFTI
     * @param array $expectedDTI
     *
     * @dataProvider typeInfoDataProvider
     */
    public function testTypeInfo($variables, $functions, $expectedVTI, $expectedFTI, $expectedDTI)
    {
        $context = new DynamicContext($variables, $functions);
        $descriptor = $context->getContextDescriptor();

        $this->assertEquals($expectedVTI, $descriptor->getVariableTypeInfo());
        $this->assertEquals($expectedFTI, $descriptor->getFunctionTypeInfo());
        $this->assertEquals($expectedDTI, $descriptor->getDetailedTypeInfo());
    }

    public function typeInfoDataProvider()
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
            'GenericRule object test' => [
                '$variables' => ['rule' => new GenericRule(0, '', '')],
                '$functions' => [],
                '$expectedVTI' => [
                    'rule' => new TypeInfoMember('rule', [GenericRule::class]),
                ],
                '$expectedFTI' => [],
                '$expectedDTI' => [
                    GenericRule::class => new TypeInfoClass(
                        GenericRule::class,
                        [
                            new TypeInfoMember('getId', ['method'], '<div class="cm-signature"><span class="type"></span> <span class="name">getId</span>(<span class="args"></span>)</span></div>'),
                            new TypeInfoMember('getName', ['method'], '<div class="cm-signature"><span class="type"></span> <span class="name">getName</span>(<span class="args"></span>)</span></div>'),
                            new TypeInfoMember('getCondition', ['method'], '<div class="cm-signature"><span class="type"></span> <span class="name">getCondition</span>(<span class="args"></span>)</span></div>'),
                        ]
                    ),
                ],
            ],
            'Functions and methods test' => [
                '$variables' => [],
                '$functions' => [
                    'round' => 'round',
                    'now' => [new \DateTime(), 'getTimestamp'],
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
