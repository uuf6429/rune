<?php

namespace uuf6429\Rune\Action;

abstract class AbstractConfigurableAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($eval, $context, $rule)
    {
        $configExpr = $this->getConfigExpression();
        echo var_export($configExpr, true).' => ';
        $config = is_null($configExpr) ? [] : $eval->evaluate($configExpr);
        print_r($config);
        $this->executeWithConfig($eval, $context, $rule, (array) $config);
    }

    /**
     * @return array Array of [name => expression] pairs.
     */
    abstract protected function getConfigDefinition();

    /**
     * Do whatever you want here.
     *
     * @param EvaluatorInterface $eval    Evaluator instance.
     * @param ContextInterface   $context The current context.
     * @param RuleInterface      $rule    The rule that triggered this action.
     * @param array              $config  Array of [name => value] pairs.
     */
    abstract protected function executeWithConfig($eval, $context, $rule, $config);

    /**
     * Returns config expression or null if not applicable.
     *
     * @return string|null
     */
    private function getConfigExpression()
    {
        $result = [];

        foreach ($this->getConfigDefinition() as $name => $expression) {
            if (trim($expression) === '') {
                $expression = 'null';
            }
            $result[] = sprintf(
                '%s: (%s)',
                var_export($name, true),
                $expression
            );
        }

        return count($result)
            ? ('{'.implode(', ', $result).'}')
            : null;
    }
}
