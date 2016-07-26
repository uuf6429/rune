<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Action\ActionInterface;
use uuf6429\Rune\Context\ContextInterface;
use uuf6429\Rune\Rule\RuleInterface;
use uuf6429\Rune\Util\ContextRuleException;
use uuf6429\Rune\Util\EvaluatorInterface;
use uuf6429\Rune\Util\SymfonyEvaluator;

class Engine
{
    const ON_ERROR_FAIL_RULE = 1;
    const ON_ERROR_FAIL_CONTEXT = 2;
    const ON_ERROR_FAIL_ENGINE = 3;

    /**
     * @var \Exception[]
     */
    protected $errors;

    /**
     * @var EvaluatorInterface
     */
    protected $evaluator;

    /**
     * @var int
     */
    protected $failMode;

    /**
     * @param EvaluatorInterface|null $evaluator
     */
    public function __construct($evaluator = null)
    {
        $this->evaluator = $evaluator ?: new SymfonyEvaluator();
    }

    /**
     * @param ContextInterface                  $context
     * @param RuleInterface[]                   $rules
     * @param ActionInterface|ActionInterface[] $actions
     * @param int                               $failMode See ON_ERROR_FAIL_* constants.
     * 
     * @return int|false
     */
    public function execute($context, $rules, $actions, $failMode = self::ON_ERROR_FAIL_CONTEXT)
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        $this->failMode = $failMode;

        $descriptor = $context->getContextDescriptor();
        $this->evaluator->setVariables($descriptor->getVariables());
        $this->evaluator->setFunctions($descriptor->getFunctions());

        $matchingRules = [];
        $this->clearErrors();

        try {
            $this->findMatches($matchingRules, $context, $rules);

            // TODO implement this some time in the future
            //$this->validateMatches($matchingRules);

            $this->executeActions($actions, $context, $matchingRules);
        } catch (\Exception $ex) {
            $this->addError($ex);

            return false;
        }

        return count($matchingRules);
    }

    /**
     * @return \Exception[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    protected function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * @param \Exception $error
     */
    protected function addError(\Exception $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @param RuleInterface[]  $result
     * @param ContextInterface $context
     * @param RuleInterface[]  $rules
     */
    protected function findMatches(&$result, $context, $rules)
    {
        try {
            foreach ($rules as $rule) {
                $this->findMatchesForContextRule($result, $context, $rule);
            }
        } catch (\Exception $ex) {
            if ($this->failMode === self::ON_ERROR_FAIL_ENGINE) {
                throw $ex;
            } else {
                $this->addError($ex);
            }
        }
    }

    /**
     * @param RuleInterface[]  $result
     * @param ContextInterface $context
     * @param RuleInterface    $rule
     */
    protected function findMatchesForContextRule(&$result, $context, $rule)
    {
        try {
            $cond = $rule->getCondition();
            $match = ($cond === '') ? true : $this->evaluator->evaluate($rule->getCondition());

            if (!is_bool($match)) {
                throw new \RuntimeException(sprintf(
                    'The condition result for rule %s (%s) should be boolean, not %s.',
                    $rule->getID(),
                    $rule->getName(),
                    gettype($match)
                ));
            }

            if ($match) {
                $result[] = $rule;
            }
        } catch (\Exception $ex) {
            $ex = new ContextRuleException($context, $rule, null, $ex);

            if ($this->failMode === self::ON_ERROR_FAIL_ENGINE
                || $this->failMode === self::ON_ERROR_FAIL_CONTEXT) {
                throw $ex;
            } else {
                $this->addError($ex);
            }
        }
    }

    /**
     * @param ActionInterface[] $actions
     * @param ContextInterface  $context
     * @param RuleInterface[]   $rules
     */
    protected function executeActions($actions, $context, $rules)
    {
        foreach ($rules as $rule) {
            try {
                foreach ($actions as $action) {
                    $action->execute($this->evaluator, $context, $rule);
                }
            } catch (\Exception $ex) {
                if ($this->failMode === self::ON_ERROR_FAIL_ENGINE) {
                    throw $ex;
                } else {
                    $this->addError(new ContextRuleException($context, $rule, null, $ex));
                }
            }
        }
    }
}
