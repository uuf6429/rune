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
     * @var AbstractContext[]
     */
    protected $contexts;

    /**
     * @var AbstractRule[]
     */
    protected $rules;

    /**
     * @var \ContextRuleException[]
     */
    protected $errors;

    /**
     * @var Evaluator
     */
    protected $eval;

    /**
     * @var int
     */
    protected $failMode;

    /**
     * @param AbstractContext|AbstractContext[] $contexts
     * @param AbstractRule[]                    $rules
     * @param string                            $failMode See ON_ERROR_FAIL_* constants.
     */
    public function __construct($contexts, $rules, $failMode = self::ON_ERROR_FAIL_ENGINE)
    {
        if (!is_array($contexts)) {
            $contexts = [$contexts];
        }

        $this->contexts = $contexts;
        $this->rules = $rules;
        $this->failMode = $failMode;

        $this->eval = new Evaluator();
    }

    public function execute()
    {
        $matches = [];
        $this->clearErrors();

        try {
            $this->findMatches($matches);
        } catch (\Exception $ex) {
            if ($this->failMode === self::ON_ERROR_FAIL_ENGINE) {
                throw $ex;
            } else {
                $this->errors[] = $ex;
            }
        }

        // TODO implement this some time in the future
        //$this->validateMatches($matches);

        $this->executeMatches($matches);
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
     * @param ContextRulePair[] $result
     */
    protected function findMatches(&$result)
    {
        try {
            foreach ($this->contexts as $context) {
                $this->findMatchesForContext($result, $context);
            }
        } catch (\Exception $ex) {
            if ($this->failMode > self::ON_ERROR_FAIL_ENGINE) {
                throw $ex;
            } else {
                $this->errors[] = $ex;
            }
        }
    }

    /**
     * @param ContextRulePair[] $result
     * @param AbstractContext   $context
     */
    protected function findMatchesForContext(&$result, $context)
    {
        try {
            $this->eval->setFields($context->getFields());

            foreach ($this->rules as $rule) {
                $this->findMatchesForContextRule($result, $context, $rule);
            }
        } catch (\Exception $ex) {
            if ($this->failMode > self::ON_ERROR_FAIL_CONTEXT) {
                throw $ex;
            } else {
                $this->errors[] = $ex;
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
            $match = ($cond === '') ? true : $this->eval->evaluate($rule->getCondition());

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
            if ($this->failMode > self::ON_ERROR_FAIL_RULE) {
                throw $ex;
            } else {
                $pair = new ContextRulePair($context, $rule);
                $this->errors[] = new ContextRuleException($pair, null, $ex);
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
                $this->eval->setFields($context->getFields());
                $context->execute($this->eval, $match->getRule());
            } catch (\Exception $ex) {
                $this->errors[] = new ContextRuleException($match, null, $ex);
            }
        }
    }
}
