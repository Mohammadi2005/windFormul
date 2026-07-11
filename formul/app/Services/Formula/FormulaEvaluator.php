<?php

namespace App\Services\Formula;

use Exception;

class FormulaEvaluator
{
    public function evaluate(array $node, array $variables): float|int
    {
        switch ($node['type']) {

            case 'number':
                return $node['value'];

            case 'variable':

                $id = $node['variable_id'];

                if (! array_key_exists($id, $variables)) {
                    throw new Exception("Variable {$id} not found.");
                }

                return $variables[$id];
    
            case 'operator':
    
                $left = $this->evaluate($node['left'], $variables);

                $right = $this->evaluate($node['right'], $variables);

                return $this->calculate(
                    $node['operator'],
                    $left,
                    $right
                );
        }

        throw new Exception("Unknown node type.");
    }

    protected function calculate(
        string $operator,
        float|int $left,
        float|int $right
    ): float|int {

        return match ($operator) {

            '+' => $left + $right,

            '-' => $left - $right,

            '*' => $left * $right,

            '/' => $right == 0
                    ? throw new Exception('Division by zero.')
                    : $left / $right,

            default => throw new Exception("Unknown operator {$operator}")
        };
    }
}