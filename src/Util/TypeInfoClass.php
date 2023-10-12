<?php declare(strict_types=1);

namespace uuf6429\Rune\Util;

class TypeInfoClass
{
    protected string $name;

    /**
     * @var TypeInfoMember[]
     */
    protected array $members = [];

    protected ?string $hint;

    protected ?string $link;

    /**
     * @param TypeInfoMember[] $members
     */
    public function __construct(string $name, array $members, ?string $hint = null, ?string $link = null)
    {
        $this->name = $name;
        $this->members = array_combine(
            array_map(
                static fn (TypeInfoMember $member) => $member->getName(),
                $members
            ),
            $members
        );
        $this->hint = $hint;
        $this->link = $link;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TypeInfoMember[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function hasHint(): bool
    {
        return (bool)strlen(trim((string)$this->hint));
    }

    public function getHint(): ?string
    {
        return $this->hasHint() ? $this->hint : null;
    }

    public function hasLink(): bool
    {
        return (bool)strlen(trim((string)$this->link));
    }

    public function getLink(): ?string
    {
        return $this->hasLink() ? $this->link : null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'members' => array_map(
                static fn (TypeInfoMember $member) => $member->toArray(),
                $this->getMembers()
            ),
            'hint' => $this->getHint(),
            'link' => $this->getLink(),
        ];
    }
}
