<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\ContextRuleException;
use uuf6429\Rune\Util\ContextRulePair;
use uuf6429\Rune\Util\Evaluator;

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
     * @var Evaluator
     */
    protected $evaluator;

    /**
     * @var int
     */
    protected $failMode;

    /**
     * @param AbstractContext|AbstractContext[] $contexts
     * @param AbstractRule[]                    $rules
     * @param string                            $failMode See ON_ERROR_FAIL_* constants.
     * 
     * @return int|false
     */
    public function execute($contexts, $rules, $failMode = self::ON_ERROR_FAIL_CONTEXT)
    {
        if (!is_array($contexts)) {
            $contexts = [$contexts];
        }

        $this->failMode = $failMode;

        $matches = [];
        $this->clearErrors();

        try {
            $this->findMatches($matches, $contexts, $rules);

            // TODO implement this some time in the future
            //$this->validateMatches($matches);

            $this->executeMatches($matches);
        } catch (\Exception $ex) {
            $this->addError($ex);

            return false;
        }

        return count($matches);
    }

    /**
     * @return Evaluator
     */
    protected function getEvaluator()
    {
        if (!$this->evaluator) {
            $this->evaluator = new Evaluator();
        }

        return $this->evaluator;
    }

    /**
     * @return \ContextRuleException[]
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
     * @param ContextRulePair[] $result
     * @param AbstractContext[] $contexts
     * @param AbstractRule[]    $rules
     */
    protected function findMatches(&$result, $contexts, $rules)
    {
        try {
            foreach ($contexts as $context) {
                $this->findMatchesForContext($result, $context, $rules);
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
     * @param ContextRulePair[] $result
     * @param AbstractContext   $context
     * @param AbstractRule[]    $rules
     */
    protected function findMatchesForContext(&$result, $context, $rules)
    {
        try {
            $this->getEvaluator()->setVariables($context->getVariables());

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
     * @param ContextRulePair[] $result
     * @param AbstractContext   $context
     * @param AbstractRule      $rule
     */
    protected function findMatchesForContextRule(&$result, $context, $rule)
    {
        try {
            $cond = $rule->getCondition();
            $match = ($cond === '') ? true : $this->getEvaluator()->evaluate($rule->getCondition());

            if (!is_bool($match)) {
                throw new \RuntimeException(sprintf(
                    'The condition result for rule %s (%s) should be boolean, not %s.',
                    $rule->getID(),
                    $rule->getName(),
                    gettype($match)
                ));
            }

            if ($match) {
                $result[] = new ContextRulePair($context, $rule);
            }
        } catch (\Exception $ex) {
            $pair = new ContextRulePair($context, $rule);
            $ex = new ContextRuleException($pair, null, $ex);

            if ($this->failMode === self::ON_ERROR_FAIL_ENGINE
                || $this->failMode === self::ON_ERROR_FAIL_CONTEXT) {
                throw $ex;
            } else {
                $this->addError($ex);
            }
        }
    }

    /**
     * @param ContextRulePair[] $matches
     */
    protected function executeMatches($matches)
    {
        foreach ($matches as $match) {
            try {
                $context = $match->getContext();
                $this->getEvaluator()->setVariables($context->getVariables());
                $context->execute($this->getEvaluator(), $match->getRule());
            } catch (\Exception $ex) {
                if ($this->failMode === self::ON_ERROR_FAIL_ENGINE) {
                    throw $ex;
                } else {
                    $this->addError(new ContextRuleException($match, null, $ex));
                }
            }
        }
    }
}
