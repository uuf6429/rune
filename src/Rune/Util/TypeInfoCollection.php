<?php

namespace uuf6429\Rune\Util;

class TypeInfoCollection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var TypeInfoInterface[]
     */
    protected $items = [];

    /**
     * @param TypeInfoInterface[] $items
     */
    public function __construct(array $items = [])
    {
        array_map([$this, 'checkValue'], $items);

        $this->items = array_values($items);
    }

    /**
     * @param TypeInfoInterface $value
     */
    public function checkValue(TypeInfoInterface $value)
    {
        // lol
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        if (is_string($offset)) {
            trigger_error(
                'Accessing TypeInfoCollection by a string key is deprecated and will be removed in the future.',
                E_USER_DEPRECATED
            );

            $offset = $this->findIndexByName($offset);
        }

        return array_key_exists($offset, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (is_string($offset)) {
            trigger_error(
                'Accessing TypeInfoCollection array by a string key is deprecated and will be removed in the future.',
                E_USER_DEPRECATED
            );

            $offset = $this->findIndexByName($offset);
        }

        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_string($offset)) {
            trigger_error(
                'Accessing TypeInfoCollection array by a string key is deprecated and will be removed in the future.',
                E_USER_DEPRECATED
            );

            $offset = $this->findIndexByName($offset);
        }

        $this->checkValue($value);

        $this->items[$offset] = $value;
    }

    /**
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (is_string($offset)) {
            trigger_error(
                'Accessing TypeInfoCollection array by a string key is deprecated and will be removed in the future.',
                E_USER_DEPRECATED
            );

            $offset = $this->findIndexByName($offset);
        }

        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param string $name
     *
     * @return int the index of the first item matching $name, otherwise -1 is returned
     */
    public function findIndexByName($name)
    {
        foreach ($this->items as $index => $item) {
            if ($item->getName() === $name) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @param string $name
     *
     * @return TypeInfoInterface the first item matching $name, otherwise null is returned
     */
    public function findByName($name)
    {
        foreach ($this->items as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param self|\Traversable|array $otherCollection
     *
     * @return self
     */
    public function merge($otherCollection)
    {
        if ($otherCollection instanceof \Traversable) {
            $otherCollection = iterator_to_array($otherCollection);
        }

        if (!is_array($otherCollection)) {
            throw new \InvalidArgumentException('An array was expected');
        }

        return new self(array_merge($this->toArray(), $otherCollection));
    }

    /**
     * @return TypeInfoInterface[]
     */
    public function toArray()
    {
        return array_values($this->items);
    }

    /**
     * @return array<string, TypeInfoInterface>
     */
    public function toNameArray()
    {
        $items = $this->toArray();

        return array_combine(
            array_map(
                function (TypeInfoInterface $item) {
                    return $item->getName();
                },
                $items
            ),
            $items
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->items));
    }
}
