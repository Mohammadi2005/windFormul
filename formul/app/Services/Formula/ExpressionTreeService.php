<?php

namespace App\Services\Formula;

class ExpressionTreeService
{
    public static function toTokens(array $node): array
    {
        $tokens = [];

        self::walk($node, $tokens, false);

        return $tokens;
    }

    private static function walk(array $node, array &$tokens, bool $parentIsOperator)
    {
        if ($node['type'] === 'variable') {
            $tokens[] = [
                'type' => 'variable',
                'variable_id' => $node['variable_id']
            ];

            return;
        }

        // operator

        if ($parentIsOperator) {
            $tokens[] = [
                'type' => 'left_parenthesis'
            ];
        }

        self::walk($node['left'], $tokens, true);

        $tokens[] = [
            'type' => 'operator',
            'value' => $node['operator']
        ];

        self::walk($node['right'], $tokens, true);

        if ($parentIsOperator) {
            $tokens[] = [
                'type' => 'right_parenthesis'
            ];
        }
    }
}