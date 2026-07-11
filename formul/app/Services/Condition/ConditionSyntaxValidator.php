<?php

namespace App\Services\Condition;

use Exception;

class ConditionSyntaxValidator
{
    /**
     * Only Syntax Validation
     */
    public function validate(array $tokens): void
    {
        if (empty($tokens)) {
            throw new Exception('Condition is empty.');
        }

        $balance = 0;
        $previous = null;

        foreach ($tokens as $token) {

            switch ($token['type']) {

                case 'left_parenthesis':
                    $balance++;
                    break;

                case 'right_parenthesis':
                    $balance--;

                    if ($balance < 0) {
                        throw new Exception('Invalid parentheses.');
                    }

                    break;
            }

            // بررسی ترتیب مجاز Token ها
            if ($previous) {

                // دو عملگر پشت سر هم مجاز نیست
                if (
                    $previous['type'] === 'operator' &&
                    $token['type'] === 'operator'
                ) {
                    throw new Exception('Two operators cannot be adjacent.');
                }

                // دو مقدار پشت سر هم مجاز نیست
                if (
                    in_array($previous['type'], ['variable', 'number', 'constant']) &&
                    in_array($token['type'], ['variable', 'number', 'constant'])
                ) {
                    throw new Exception('Two operands cannot be adjacent.');
                }

                // بعد از ( نمی‌توان عملگر یا ) داشت
                if (
                    $previous['type'] === 'left_parenthesis' &&
                    in_array($token['type'], ['operator', 'right_parenthesis'])
                ) {
                    throw new Exception('Invalid token after left parenthesis.');
                }

                // قبل از ) نمی‌توان عملگر یا ( داشت
                if (
                    $token['type'] === 'right_parenthesis' &&
                    in_array($previous['type'], ['operator', 'left_parenthesis'])
                ) {
                    throw new Exception('Invalid token before right parenthesis.');
                }
            }

            $previous = $token;
        }

        if ($balance !== 0) {
            throw new Exception('Parentheses are not balanced.');
        }

        if ($tokens[0]['type'] === 'operator') {
            throw new Exception('Condition cannot start with operator.');
        }

        if ($tokens[0]['type'] === 'right_parenthesis') {
            throw new Exception('Condition cannot start with right parenthesis.');
        }

        if (last($tokens)['type'] === 'operator') {
            throw new Exception('Condition cannot end with operator.');
        }

        if (last($tokens)['type'] === 'left_parenthesis') {
            throw new Exception('Condition cannot end with left parenthesis.');
        }
    }
}