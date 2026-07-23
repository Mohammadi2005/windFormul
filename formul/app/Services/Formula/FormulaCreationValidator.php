<?php

namespace App\Services\Formula;

use App\Models\Formula;
use App\Models\Variable;
use Exception;

class FormulaCreationValidator
{
    /**
     * بررسی می‌کند که تمام Formula Variable ها
     * قبلاً برای این Window Type تولید شده باشند.
     */
    public function validate(
        int $windowTypeId,
        array $dependencyIds,
        int $outputVariableId
    ): void {

        foreach ($dependencyIds as $variableId) {

            /*
             * اطلاعات متغیر
             */
            $variable = Variable::find($variableId);

            if (!$variable) {
                throw new Exception("Variable {$variableId} not found.");
            }

            /*
             * Input همیشه مجاز است.
             */
            if ($variable->type === 'input') {
                continue;
            }

            if ($variable->type === 'constant') {
                if ($variable->default_value === null) {
                    throw new Exception(
                        "Constant '{$variable->code}' has no default value."
                    );
                }
                continue;
            }

            if ($variable->type === 'supplier') {
                continue;
            }
            /*
             * اگر خود متغیر خروجی همین فرمول باشد
             * مثل A = A + 1
             * فعلاً اجازه نمی‌دهیم.
             */
            if ($variableId == $outputVariableId) {

                throw new Exception(
                    "Variable {$variable->code} cannot depend on itself."
                );
            }

            /*
             * آیا قبلاً برای همین نوع پنجره
             * فرمولی این متغیر را تولید کرده؟
             */
            $exists = Formula::where('window_type_id', $windowTypeId)
                ->where('output_variable_id', $variableId)
                ->exists();

            if (!$exists) {

                throw new Exception(
                    "Variable '{$variable->code}' is Formula type but has not been generated yet."
                );
            }
        }
    }
}