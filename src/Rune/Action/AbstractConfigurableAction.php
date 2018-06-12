<?php

namespace uuf6429\Rune\Action;

use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\EvaluatorInterface;

abstract class AbstractConfigurableAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    final public function execute($eval, $context, $rule)
    {
        $configExpr = $this->getConfigExpression();
        $config = $configExpr === null ? [] : $eval->evaluate($configExpr);
        $this->executeWithConfig($eval, $context, $rule, (array) $config);
    }

    /**
     * Return an array of configuration name/expression pairs.
     * You will receive the value of the evaluated expressions later on in executeWithConfig().
     *
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
            ? ('{' . implode(', ', $result) . '}')
            : null;
    }
}
