<?php

namespace uuf6429\Rune\Util;

class TypeInfoClass implements TypeInfoInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $hint;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var TypeInfoMember[]
     */
    protected $members = [];

    /**
     * @param string           $name
     * @param TypeInfoMember[] $members
     * @param string           $hint
     * @param string           $link
     *
     * @todo Change position of $members
     */
    public function __construct($name, array $members = [], $hint = '', $link = '')
    {
        $this->name = $name;
        $this->hint = $hint;
        $this->link = $link;

        foreach ($members as $member) {
            $this->members[$member->getName()] = $member;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasHint()
    {
        return (bool) $this->hint;
    }

    /**
     * @return string
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return TypeInfoMember[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'hint' => $this->hint,
            'link' => $this->link,
            'members' => $this->members,
        ];
    }
}
