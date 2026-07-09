<?php

namespace App\Services\Formula;

class AstBuilder
{
    // عبارت postfix رو به درخت تبدیل میکنه
    public function build(array $postfix): array
    {
        $stack = [];

        foreach ($postfix as $token) {

            // اگر عملگر نباشه تو پشته قرار داده میشه
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
            
            // ... اگر عملگر باشه 

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

        // وقتی که عبارت postFix تموم بشه دیگه درخت ایجاد شده و به صورت تک ایتم ریخته میشه توی پشته
        return array_pop($stack);
    }
}