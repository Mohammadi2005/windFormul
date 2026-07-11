<?php

namespace App\Services\Formula;

use Exception;
use App\Models\Variable;

class ExpressionTreeService
{
    /**
     * درخت رو به لیستی از توکن های Infix تبدیل می‌کنه
     * خروجی این متد همون فرمتی است که فرانت‌ برای مشاهده و یا ویرایش فرمول انتظار داره
     */
    public static function toTokens(array $node): array
    {
        $tokens = [];

        self::walk($node, $tokens, false);

        return $tokens;
    }

    /**
     * درخت رو به صورت بازگشتی پیمایش می‌کنه و معادل Infix اون رو
     * به صورت توکن تولید می‌کنه
     * در صورت قرار گرفتن یک عملگر داخل عملگر دیگه پرانتز اضافه می‌شود تا
     * ترتیب اجرای عملیات دقیقاً مشابه فرمول اصلی حفظ بشه
     */
    private static function walk(array $node, array &$tokens, bool $parentIsOperator)
    {

        if ($node['type'] === 'variable') {
            $tokens[] = [
                'type' => 'variable',
                'variable_id' => $node['variable_id']
            ];

            return;
        }

        if ($node['type'] === 'number') {

            $tokens[] = [
                'type' => 'number',
                'value' => $node['value'],
            ];

            return;
        }

        // operator

        // اگر گره فعلی زیرمجموعه یک عملگر دیگه باشه برای حفظ تقدم عملگرها
        // قبل از پیمایش فرزندان یک پرانتز باز ایجاد می‌کنم
        if ($parentIsOperator) {
            $tokens[] = [
                'type' => 'left_parenthesis'
            ];
        }

        // ترتیب پیمایش به صورت:
        // زیر شاخه چپ -> عملگر -> زیرشاخه راست
        // هست تا خروجی به فرم Infix تولید بشه
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

    // توی این متد من درخت فرمول رو میگیرم و میخوام به یک رشته تبدیلش کنم 
    public static function toExpression(array $node): string
    {
        
        //  ... برای گره هایی که
        switch ($node['type']) {

            // عدد هستن عدد رو میزارم تو رشته
            case 'number':
                return (string) $node['value'];

            // متغییر ها هم نمادش رو لازم دارم برای رشته
            case 'variable':
                if (isset($node['code'])) {
                    return $node['code'];
                }

                return Variable::find($node['variable_id'])->code;

                
            // گره‌های عملگر مستقیماً نماد متغیرها رو ندارن 
            // هر دو گره فرزند رو به صورت بازگشتی پیمایش می کنم تا نماد متغییر های وابسته رو جمع‌آوری کنم
            case 'operator':

                $left = self::toExpression($node['left']);

                $right = self::toExpression($node['right']);

                return '(' . $left . ' ' . $node['operator'] . ' ' . $right . ')';
        }

        throw new Exception('Unknown node type');
    }

}

