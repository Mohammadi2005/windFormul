<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Formula\FormulaService;
use App\Services\Formula\FormulaSelector;
use App\Services\Condition\ConditionService;
use App\Services\Formula\FormulaEvaluator;
use App\Http\Requests\StoreFormulaRequest;
use App\Services\Formula\ExpressionTreeService;
use App\Services\Formula\FormulaDependencyValidator;
use App\Models\Formula;
use App\Models\FormulaDependency;

use App\Services\Condition\ConditionEvaluator;
use App\Services\Formula\FormulaExecutionPlanner;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class FormulController extends Controller
{
    // create object from FormulaService class with Dependency Injection opstion
    public function store(StoreFormulaRequest $request, FormulaService $formulaService)
    {
        $formula = $formulaService->create($request->validated());
        return response()->json($formula);
    }

    public function show()
    {
        $formula = Formula::find(6);
        $expression = ExpressionTreeService::toExpression($formula->expression_json);
        return response()->json([
            'id' => $formula->id,
            'window_type' => $formula->windowType->name,
            'title' => $formula->resultVariable->title,
            'code' => $formula->resultVariable->code,
            'tokens' => ExpressionTreeService::toTokens($formula->expression_json),
            'expression' => "{$formula->resultVariable->code} = {$expression}",
        ]);
    }

    public function calc(){
        $formula = Formula::findOrFail(3);

        $variables = [
            4 => 22,
            8 => 1,
            9 => 9,
            10 => 3,
            7 => 2,
        ];

        $evaluator = new FormulaEvaluator(); 
        $result = $evaluator->evaluate($formula->expression_json, $variables);

        dd($result);
    }

    // دریافت ورودی پروژه.
    // بارگذاری فرمول‌ها.
    // انتخاب فرمول‌های فعال بر اساس شرط‌ها.
    // ساخت گراف وابستگی و مرتب‌سازی.
    // اجرای محاسبات برای هر سکشن.
    // ذخیره نتایج.
    public function calculate(Request $request): array
    {
        $sections = $request->sections;
        // dd($project);
        $conditionEvaluator = new ConditionEvaluator();
        $formulaEvaluator = new FormulaEvaluator();


        $result = [];

        foreach ($sections as $section) {

            $variables = $section['variables'];

            $context = [
                'i'   => $section['i'],
                'j'   => $section['j'],
                'max' => $request->rows,
            ];


            // تمام فرمول‌هایی که خروجی آن‌ها متغیر شماره 1 است
            $formulas = Formula::where('window_type_id', $request->window_type_id)
                ->where('output_variable_id', 1)
                ->where('is_active', true)
                ->orderBy('execution_order')
                ->get();

            $value = null;

            foreach ($formulas as $formula) {


                
                $condition = $conditionEvaluator->evaluate(
                    $formula->condition_json,
                    $context
                );

                if (!$condition) {
                    continue;
                }

                $value = $formulaEvaluator->evaluate(
                    $formula->expression_json,
                    $variables
                );

                // اولین فرمول معتبر اجرا می‌شود
                break;
            }

            $result[] = [
                'i' => $section['i'],
                'j' => $section['j'],
                'value' => $value,
            ];
        }

        return $result;
    }

    public function selectFormulas(Request $request,FormulaSelector $selector, FormulaExecutionPlanner $planner) {

        $formulas = Formula::where('window_type_id', $request->window_type_id)->where('is_active', true)->get();

        $dependencies = FormulaDependency::whereIn(
                'formula_id',
                $formulas->pluck('id')
            )
            ->where('type', 'input')
            ->get()
            ->groupBy('formula_id');

            $selected = $selector->select(
                formulas: $formulas,
                sections: $request->sections,
                rows: $request->rows,
                columns: $request->columns
            );

        $planned = $planner->plan(

            selectedSections: $selected,

            dependencies: $dependencies

        );

        return response()->json(

            collect($planned)->map(function ($item) {

                return [

                    'section' => $item['section'],

                    'execution_plan' => collect($item['formulas'])->map(function ($formula) {

                        return [

                            'id' => $formula->id,

                            'expression' =>
                                $formula->resultVariable->code .
                                ' = ' .
                                ExpressionTreeService::toExpression(
                                    $formula->expression_json
                                ),

                        ];

                    }),

                ];

            })

        );
    }

    
    public function selectFormulasWithValidator(
        Request $request,
        FormulaSelector $selector, 
        FormulaExecutionPlanner $planner,
        FormulaDependencyValidator $validator
        
        ) {

        $formulas = Formula::where('window_type_id', $request->window_type_id)->where('is_active', true)->get();

        $dependencies = FormulaDependency::whereIn(
                'formula_id',
                $formulas->pluck('id')
            )
            ->where('type', 'input')
            ->get()
            ->groupBy('formula_id');

            $selected = $selector->select(
                formulas: $formulas,
                sections: $request->sections,
                rows: $request->rows,
                columns: $request->columns
            );
        
        $planned = $planner->plan(
            selectedSections: $selected,
            dependencies: $dependencies
        );

        

        foreach ($planned as $index => $section) {

            $validator->validate(

                executionPlan: $section['formulas'],

                dependencies: $dependencies,

                variables: $request->sections[$index]['variables']

            );
        }

        return response()->json(

            collect($planned)->map(function ($item) {

                return [

                    'section' => $item['section'],

                    'execution_plan' => collect($item['formulas'])->map(function ($formula) {

                        return [

                            'id' => $formula->id,

                            'expression' =>
                                $formula->resultVariable->code .
                                ' = ' .
                                ExpressionTreeService::toExpression(
                                    $formula->expression_json
                                ),

                        ];

                    }),

                ];

            })

        );
    }
}
