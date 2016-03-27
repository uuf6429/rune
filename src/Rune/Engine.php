<?php

namespace uuf6429\Rune;

use uuf6429\Rune\Context\AbstractContext;
use uuf6429\Rune\Rule\AbstractRule;
use uuf6429\Rune\Util\ContextRuleException;
use uuf6429\Rune\Util\ContextRulePair;
use uuf6429\Rune\Util\Evaluator;

class Engine
{
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
     * @param AbstractContext|AbstractContext[] $contexts
     * @param AbstractRule[]                    $rules
     */
    public function __construct($contexts, $rules)
    {
        if (!is_array($contexts)) {
            $contexts = [$contexts];
        }

        $this->contexts = $contexts;
        $this->rules = $rules;
        $this->eval = new Evaluator();
    }

    public function execute()
    {
        $this->errors = [];
        $matches = $this->findMatches();

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

    /**
     * @return ContextRulePair[]
     */
    protected function findMatches()
    {
        $result = [];

        foreach ($this->contexts as $context) {
            $this->eval->setFields($context->getFields());

            foreach ($this->rules as $rule) {
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
                    $pair = new ContextRulePair($context, $rule);
                    $this->errors[] = new ContextRuleException($pair, null, $ex);
                }
            }
        }

        return $result;
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
