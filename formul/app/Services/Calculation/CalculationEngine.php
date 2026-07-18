<?php

namespace App\Services\Calculation;

use App\Models\Formula;
use App\Services\Condition\ConditionEvaluator;

class CalculationEngine
{
    protected ConditionEvaluator $conditionEvaluator;

    public function __construct()
    {
        $this->conditionEvaluator = new ConditionEvaluator();
    }

    public function calculate(array $project): array
    {
        // دریافت تمام فرمول های مربوط به این نوع پنجره
        $formulas = $this->loadFormulas(
            $project['window_type_id']
        );

        $result = [];

        // روی تمام سکشن ها حلقه میزنیم
        foreach ($project['sections'] as $section) {

            // اطلاعات لازم برای شرط ها
            $context = $this->buildContext(
                $project,
                $section
            );

            // انتخاب فرمول معتبر هر خروجی
            $activeFormulas = $this->selectActiveFormulas(
                $formulas,
                $context
            );

            $result[] = [

                'section' => $section,

                'formulas' => $activeFormulas

            ];
        }

        return $result;
    }

    protected function loadFormulas(int $windowTypeId) {
        return Formula::where(
                'window_type_id',
                $windowTypeId
            )
            ->where(
                'is_active',
                true
            )
            ->get()
            ->groupBy('output_variable_id');
    }

    protected function buildContext(array $project,array $section): array
    {
        return [

            'i' => $section['i'],

            'j' => $section['j'],

            'max' => $project['rows']

        ];
    }
    protected function selectActiveFormulas($formulas,array $context): array
    {
        $activeFormulas = [];

        // برای هر خروجی
        foreach ($formulas as $outputVariableId => $formulaList) {

            // تمام فرمول های آن خروجی را بررسی میکنیم
            foreach ($formulaList as $formula) {

                $condition = $this->conditionEvaluator->evaluate(

                    $formula->condition_json,

                    $context

                );

                // اگر شرط برقرار نبود
                if (!$condition) {
                    continue;
                }

                // اولین فرمول معتبر انتخاب میشود
                $activeFormulas[$outputVariableId] = $formula;

                break;
            }
        }

        return $activeFormulas;
    }
}