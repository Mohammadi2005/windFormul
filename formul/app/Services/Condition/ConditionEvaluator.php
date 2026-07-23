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


    protected function resolveConstant(
        string $constant,
        array $context
    ): mixed {

        return match ($constant) {

            'min_i' => $context['min_i'],
            'max_i' => $context['max_i'],
            'min_j' => $context['min_j'],
            'max_j' => $context['max_j'],

            default =>
                throw new Exception("Unknown constant {$constant}")
        };
    }
}