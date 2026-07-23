<?php

namespace App\Services\Condition;

use Exception;

class ConditionTreeService
{
    public static function toExpression(array $node): string
    {

        switch ($node['type']) {
            case 'number':
                return $node['value'];
            case 'constant':
                return $node['value'];
            case 'variable':
                return $node['name'];
            case 'operator':
                $left = self::toExpression(
                    $node['left']
                );
                $right = self::toExpression(
                    $node['right']
                );
                return '(' .
                    $left .
                    ' ' .
                    $node['operator'] .
                    ' ' .
                    $right .
                    ')';
        }
        throw new Exception(
            'Unknown condition node'
        );
    }
}