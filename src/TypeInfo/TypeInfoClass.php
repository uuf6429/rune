<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

class TypeInfoClass extends TypeInfoBase
{
    /**
     * @var TypeInfoMember[]
     */
    protected array $members = [];

    /**
     * @param TypeInfoMember[] $members
     */
    public function __construct(string $name, array $members, ?string $hint = null, ?string $link = null)
    {
        parent::__construct($name, $hint, $link);

        $this->members = array_combine(
            array_map(
                static fn (TypeInfoMember $member) => $member->getName(),
                $members
            ),
            $members
        );
    }

    /**
     * @return TypeInfoMember[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'members' => array_map(
                static fn (TypeInfoMember $member) => $member->toArray(),
                $this->getMembers()
            ),
        ];
    }
}
