<?php

namespace App\Services\Formula;

class ShuntingYard
{
    protected array $precedence = [
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2,
    ];

    public function convert(array $tokens): array
    {
        $output = [];
        $stack = [];

        foreach ($tokens as $token) {

            switch ($token['type']) {

                case 'variable':
                case 'number':
                    $output[] = $token;
                    break;

                case 'operator':

                    while (
                        !empty($stack) &&
                        end($stack)['type'] === 'operator' &&
                        $this->precedence[end($stack)['value']] >= $this->precedence[$token['value']]
                    ) {
                        $output[] = array_pop($stack);
                    }

                    $stack[] = $token;

                    break;

                case 'left_parenthesis':

                    $stack[] = $token;

                    break;

                case 'right_parenthesis':

                    while (
                        !empty($stack) &&
                        end($stack)['type'] !== 'left_parenthesis'
                    ) {
                        $output[] = array_pop($stack);
                    }

                    array_pop($stack);

                    break;
            }
        }

        while (!empty($stack)) {
            $output[] = array_pop($stack);
        }

        return $output;
    }
}