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
    final public function execute(EvaluatorInterface $eval, ContextInterface $context, RuleInterface $rule): void
    {
        $configExpr = $this->getConfigExpression();
        $config = $configExpr === null ? [] : $eval->evaluate($configExpr);
        $this->executeWithConfig($eval, $context, $rule, (array)$config);
    }

    /**
     * Return an array of configuration name/expression pairs.
     * You will receive the value of the evaluated expressions later on in executeWithConfig().
     *
     * @return array array of [name => expression] pairs
     */
    abstract protected function getConfigDefinition(): array;

    /**
     * Do whatever you want here.
     *
     * @param EvaluatorInterface $eval evaluator instance
     * @param ContextInterface $context the current context
     * @param RuleInterface $rule the rule that triggered this action
     * @param array $config array of [name => value] pairs
     */
    abstract protected function executeWithConfig(
        EvaluatorInterface $eval,
        ContextInterface   $context,
        RuleInterface      $rule,
        array              $config
    ): void;

    /**
     * Returns config expression or null if not applicable.
     */
    private function getConfigExpression(): ?string
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
