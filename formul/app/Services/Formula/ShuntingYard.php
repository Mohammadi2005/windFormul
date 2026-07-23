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
        // برای تبدیل به output , stack نیاز داریم
        $output = [];
        $stack = [];

        foreach ($tokens as $token) {
            switch ($token['type']) {
                // اعداد و متغییر ها توی output ریخته میشن
                case 'variable':
                case 'number':
                    $output[] = $token;
                    break;
                // وقتی توی عبارت به عملگر میرسیم ، تا زمانی که پشته خالی نشده و ایتم بالای پشته یک عملگر هست
                // و اولویت اون عملگر بیشتر مساوی عملگر جدید هست عملگر رو از روی پشته برمی داریم
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
                // اگر در عبارت بع پرانتز باز برسیم میزاریمش تو پشته
                case 'left_parenthesis':
                    $stack[] = $token;
                    break;
                // اگر توی عبارت به پرانتز بسته برسیم تا زمانی که توی
                // پشته به پرانتز باز برسیم عملگر ها رو از پشته برمیداریم
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
        // وقتی که عبارت تموم بشه هر چی توی پشته کمونده باشه رو میریزم تو output
        while (!empty($stack)) {
            $output[] = array_pop($stack);
        }
        // حالا عبارت با فرمت postfix اماده هست
        return $output;
    }
}