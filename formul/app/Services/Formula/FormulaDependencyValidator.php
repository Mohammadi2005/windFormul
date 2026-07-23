<?php

namespace App\Services\Formula;

use Exception;
use Illuminate\Support\Collection;

class FormulaDependencyValidator
{
    /**
     * بررسی می‌کند که همه وابستگی‌ها قابل تأمین باشند.
     */
    public function validate(
        array $executionPlan,
        Collection $dependencies,
        array $variables
    ): void {

        /*
         * متغیرهایی که در حال حاضر مقدار دارند
         */
        $availableVariables = array_keys($variables);

        foreach ($executionPlan as $formula) {

            /*
             * وابستگی‌های این فرمول
             */
            foreach ($dependencies[$formula->id] ?? [] as $dependency) {

                $variableId = $dependency->variable_id;

                /*
                 * اگر مقدار این متغیر هنوز وجود ندارد
                 */
                if (! in_array($variableId, $availableVariables)) {

                    throw new Exception(
                        "Formula {$formula->id} cannot be executed. Variable {$variableId} is missing."
                    );

                }

            }

            /*
             * خروجی این فرمول از این به بعد قابل استفاده است
             */
            $availableVariables[] = $formula->output_variable_id;

        }

    }
}