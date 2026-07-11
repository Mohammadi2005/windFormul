<?php

namespace App\Services\Condition;

class ConditionAstBuilder
{
    // عبارت postfix رو به درخت تبدیل میکنه
    public function build(array $postfix): array
    {
        $stack = [];

        foreach ($postfix as $token) {

            // اگر عملگر نباشه تو پشته قرار داده میشه
            switch ($token['type']) {

                case 'variable':

                    $stack[] = [
                        'type' => 'variable',
                        'name' => $token['name'],
                    ];

                    continue 2;

                case 'number':

                    $stack[] = [
                        'type' => 'number',
                        'value' => $token['value'],
                    ];

                    continue 2;

                case 'constant':

                    $stack[] = [
                        'type' => 'constant',
                        'value' => $token['value'],
                    ];

                    continue 2;
            }

            
            // ... اگر عملگر باشه 
            if (count($stack) < 2) {
                throw new \Exception('Invalid condition expression.');
            }

            // 1 - ایتم اول پشته میشه زیر شاخه سمت راست گره عملگر
            $right = array_pop($stack);

            // 2 - ایتم دوم پشته میشه زیر شاخه سمت چپ گره عملگر
            $left = array_pop($stack);

            // عملگر همراه با زیر شاخه های چپ و راستش به عنوان یک ایتم ریخته میشن توی پشته
            $stack[] = [
                'type' => 'operator',
                'operator' => $token['value'],
                'left' => $left,
                'right' => $right,
            ];
        }

        if (count($stack) !== 1) {
            throw new \Exception('Invalid condition expression.');
        }

        // وقتی که عبارت postFix تموم بشه دیگه درخت ایجاد شده و به صورت تک ایتم ریخته میشه توی پشته
        return array_pop($stack);
    }
}