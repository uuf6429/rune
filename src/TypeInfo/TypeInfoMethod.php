<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

class TypeInfoMethod extends TypeInfoBase
{
    /**
     * @var TypeInfoParameter[]
     */
    protected array $params;

    /**
     * @param TypeInfoParameter[] $params
     */
    public function __construct(string $name, array $params, ?string $hint = null, ?string $link = null)
    {
        parent::__construct($name, ['method'], $hint, $link);

        $this->params = $params;
    }

    /**
     * @return TypeInfoParameter[]
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'params' => array_map(
                static fn (TypeInfoParameter $param) => $param->toArray(),
                $this->getParameters()
            ),
        ]);
    }
}
