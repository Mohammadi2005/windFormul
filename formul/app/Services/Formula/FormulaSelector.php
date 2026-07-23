<?php

namespace App\Services\Formula;

use App\Models\Formula;
use App\Services\Condition\ConditionEvaluator;
use Illuminate\Support\Collection;

class FormulaSelector
{
    public function __construct(protected ConditionEvaluator $conditionEvaluator) 
    {}

    // public function select(int $windowTypeId, array $sections, int $rows, int $columns): array {
    public function select(Collection $formulas,array $sections,int $rows,int $columns): array {
        // $formulas = Formula::where('window_type_id', $windowTypeId)->where('is_active', true)->get();
        $result = [];
        foreach ($sections as $section) {
            $context = [
                'i' => $section['i'],
                'j' => $section['j'],

                'min_i' => 1,
                'max_i' => $rows,

                'min_j' => 1,
                'max_j' => $columns,
            ];

            $selected = [];

            foreach ($formulas as $formula) {

                if (!$formula->condition_json) {
                    $selected[] = $formula;
                    continue;
                }

                if ($this->conditionEvaluator->evaluate($formula->condition_json,$context)) {
                    $selected[] = $formula;
                }
            }

            $result[] = [
                
                'section'=>[
                    'i'=>$section['i'],
                    'j'=>$section['j'],
                ],
                'formulas'=>$selected
            ];
        }
        return $result;
    }
}