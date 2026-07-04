<?php

namespace App\Services\Formula;

use Exception;
use App\Models\Variable;

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

    
    public static function toExpression(array $node): string
    {
   
        switch ($node['type']) {
            
            case 'number':
                return (string) $node['value'];

            case 'variable':
                // اگر نام متغیر را داخل AST ذخیره کرده‌ای
                if (isset($node['code'])) {
                    return $node['code'];
                }

                // در غیر این صورت از دیتابیس بخوان
                return Variable::find($node['variable_id'])->code;

            case 'operator':

                $left = self::toExpression($node['left']);

                $right = self::toExpression($node['right']);

                return '(' . $left . ' ' . $node['operator'] . ' ' . $right . ')';
        }

        throw new Exception('Unknown node type');
    }

}

