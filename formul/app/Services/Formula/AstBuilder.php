<?php

namespace App\Services\Formula;

class AstBuilder
{
    public function build(array $postfix): array
    {
        $stack = [];

        foreach ($postfix as $token) {

            if ($token['type'] !== 'operator') {

                if ($token['type'] === 'variable') {

                    $stack[] = [
                        'type' => 'variable',
                        'variable_id' => $token['variable_id'],
                    ];

                } elseif ($token['type'] === 'number') {

                    $stack[] = [
                        'type' => 'number',
                        'value' => $token['value'],
                    ];
                }

                continue;
            }

            $right = array_pop($stack);

            $left = array_pop($stack);

            $stack[] = [
                'type' => 'operator',
                'operator' => $token['value'],
                'left' => $left,
                'right' => $right,
            ];
        }

        return array_pop($stack);
    }
}