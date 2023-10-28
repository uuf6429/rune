<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

use JetBrains\PhpStorm\ArrayShape;

class TypeInfoMethod extends TypeInfoBase
{
    /**
     * @var TypeInfoParameter[]
     */
    protected array $params;

    /**
     * @var string[]
     */
    protected array $return;

    /**
     * @param TypeInfoParameter[] $params
     * @param string[] $return
     */
    public function __construct(string $name, array $params, array $return, ?string $hint = null, ?string $link = null)
    {
        parent::__construct($name, ['method'], $hint, $link);

        $this->params = $params;
        $this->return = $return;
    }

    /**
     * @return TypeInfoParameter[]
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * @return string[]
     */
    public function getReturnTypes(): array
    {
        return $this->return;
    }

    #[ArrayShape(['name' => 'string', 'hint' => 'null|string', 'link' => 'null|string', 'params' => 'array', 'return' => 'array'])]
    public function toArray(?callable $serializer = null): array
    {
        $result = array_merge(parent::toArray($serializer), [
            'params' => array_map(
                static fn (TypeInfoParameter $param) => $param->toArray($serializer),
                $this->getParameters()
            ),
            'return' => $this->getReturnTypes(),
        ]);

        return $serializer ? $serializer($this, $result) : $result;
    }
}
