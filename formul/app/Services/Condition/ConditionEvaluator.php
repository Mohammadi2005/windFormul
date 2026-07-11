<?php

namespace App\Services\Condition;

use Exception;

class ConditionEvaluator
{
    /**
     * Evaluate condition AST
     */
    public function evaluate(array $node, array $context)
    {
        switch ($node['type']) {

            case 'number':
                return $node['value'];

            case 'constant':
                return $this->resolveConstant($node['value'], $context);

            case 'variable':

                if (!array_key_exists($node['name'], $context)) {
                    throw new Exception("Variable '{$node['name']}' not found.");
                }

                return $context[$node['name']];

            case 'operator':

                $left = $this->evaluate($node['left'], $context);

                $right = $this->evaluate($node['right'], $context);

                return $this->calculate(
                    $node['operator'],
                    $left,
                    $right
                );
        }

        throw new Exception('Unknown node type.');
    }

    /**
     * Execute condition operator
     */
    protected function calculate(
        string $operator,
        mixed $left,
        mixed $right
    ): bool {

        return match ($operator) {

            '==' => $left == $right,

            '!=' => $left != $right,

            '>'  => $left > $right,

            '>=' => $left >= $right,

            '<'  => $left < $right,

            '<=' => $left <= $right,

            'AND' => (bool)$left && (bool)$right,

            'OR'  => (bool)$left || (bool)$right,

            default => throw new Exception("Unknown operator {$operator}")
        };
    }

    /**
     * Resolve constants such as max, min, ...
     */
    protected function resolveConstant(
        string $constant,
        array $context
    ): mixed {


        return match ($constant) {

            'max' => $context['max'] ?? throw new Exception('Context value max not found.'),

            'min' => $context['min'] ?? throw new Exception('Context value min not found.'),

            default => throw new Exception("Unknown constant {$constant}")
        };
    }
}