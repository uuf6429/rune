<?php declare(strict_types=1);

namespace uuf6429\Rune\TypeInfo;

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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'params' => array_map(
                static fn (TypeInfoParameter $param) => $param->toArray(),
                $this->getParameters()
            ),
            'return' => $this->getReturnTypes(),
        ]);
    }
}
