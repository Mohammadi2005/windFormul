<?php

namespace App\Services\Formula;

use Exception;

class FormulaValidator
{
    public function validate(array $tokens): void
    {
        if (empty($tokens)) {
            throw new Exception('Formula is empty.');
        }

        $balance = 0;
        $previous = null;

        foreach ($tokens as $token) {

            if ($token['type'] === 'left_parenthesis') {
                $balance++;
            }

            if ($token['type'] === 'right_parenthesis') {
                $balance--;

                if ($balance < 0) {
                    throw new Exception('Invalid parentheses.');
                }
            }

            if (
                $previous &&
                $previous['type'] === 'operator' &&
                $token['type'] === 'operator'
            ) {
                throw new Exception('Two operators cannot be adjacent.');
            }

            $previous = $token;
        }

        if ($balance !== 0) {
            throw new Exception('Parentheses are not balanced.');
        }

        if ($tokens[0]['type'] === 'operator') {
            throw new Exception('Formula cannot start with operator.');
        }

        if (last($tokens)['type'] === 'operator') {
            throw new Exception('Formula cannot end with operator.');
        }
    }
}