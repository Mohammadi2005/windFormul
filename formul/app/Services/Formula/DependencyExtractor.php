<?php

namespace App\Services\Formula;

class DependencyExtractor
{
    public function extract(array $node): array
    {
        $variables = [];
        $this->walk($node, $variables);
        return array_values(array_unique($variables));
    }
    protected function walk(array $node, array &$variables): void
    {
        switch ($node['type']) {
            // گره‌های متغیر وابستگی‌های ورودی فرمول رو نشون میدن که ایدیشون رو نگهمیدارم
            case 'variable':
                $variables[] = $node['variable_id'];
                return;
            // گره‌های عملگر مستقیماً متغیرها رو ندارن 
            // هر دو گره فرزند را به صورت بازگشتی پیمایش می کنم تا متغییر های وابسته رو جمع‌آوری کنم.
            case 'operator':
                $this->walk($node['left'], $variables);
                $this->walk($node['right'], $variables);
                return;
            case 'number':
                return;
        }
    }
}