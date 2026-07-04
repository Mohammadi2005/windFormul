<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Formula\FormulaService;
use App\Services\Formula\FormulaEvaluator;
use App\Http\Requests\StoreFormulaRequest;
use App\Services\Formula\ExpressionTreeService;
use App\Models\Formula;

use Illuminate\Http\Request;

class FormulController extends Controller
{
    public function store(StoreFormulaRequest $request, FormulaService $service)
    {
        $formula = $service->create($request->validated());
        return response()->json($formula);
    }

    // ورودی 

    //     "window_type_id": 1,
    //     "name": "Glass Area",
    //     "code": "GA",
    //     "output_variable_id": 1,
    //     "execution_order": 1,
    //     "tokens": [
    //         { "type": "left_parenthesis" },
    //         { "type": "left_parenthesis" },
    //         { "type": "variable", "variable_id": 2 },
    //         { "type": "operator", "value": "+" },
    //         { "type": "variable", "variable_id": 3 },
    //         { "type": "right_parenthesis" },
    //         { "type": "operator", "value": "*" },
    //         { "type": "variable", "variable_id": 2 },
    //         { "type": "right_parenthesis" },
    //         { "type": "operator", "value": "-" },
    //         { "type": "variable", "variable_id": 3 }
    //     ]
    // }

    // خروجی 

    //   {
    //   "name": "Glass Area",
    //   "code": "GA",
    //   "window_type_id": 1,
    //   "output_variable_id": 1,
    //   "expression_json": {
    //     "type": "operator",
    //     "operator": "-",
    //     "left": {
    //       "type": "operator",
    //       "operator": "*",
    //       "left": {
    //         "type": "operator",
    //         "operator": "+",
    //         "left": {
    //           "type": "variable",
    //           "variable_id": 2
    //         },
    //         "right": {
    //           "type": "variable",
    //           "variable_id": 3
    //         }
    //       },
    //       "right": {
    //         "type": "variable",
    //         "variable_id": 2
    //       }
    //     },
    //     "right": {
    //       "type": "variable",
    //       "variable_id": 3
    //     }
    //   },
    //   "execution_order": 1,
    //   "is_active": true,
    //   "updated_at": "2026-07-03T19:13:11.000000Z",
    //   "created_at": "2026-07-03T19:13:11.000000Z",
    //   "id": 1
    // }
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

    //     {
    //   "id": 1,
    //   "name": "Glass Area",
    //   "code": "GA",
    //   "tokens": [
    //     {
    //       "type": "left_parenthesis"
    //     },
    //     {
    //       "type": "left_parenthesis"
    //     },
    //     {
    //       "type": "variable",
    //       "variable_id": 2
    //     },
    //     {
    //       "type": "operator",
    //       "value": "+"
    //     },
    //     {
    //       "type": "variable",
    //       "variable_id": 3
    //     },
    //     {
    //       "type": "right_parenthesis"
    //     },
    //     {
    //       "type": "operator",
    //       "value": "*"
    //     },
    //     {
    //       "type": "variable",
    //       "variable_id": 2
    //     },
    //     {
    //       "type": "right_parenthesis"
    //     },
    //     {
    //       "type": "operator",
    //       "value": "-"
    //     },
    //     {
    //       "type": "variable",
    //       "variable_id": 3
    //     }
    //   ]
    // }

    public function calc(){
        $formula = Formula::findOrFail(3);

        $variables = [
            // 1 => 1200,
            4 => 2,
            8 => 1,
            9 => 9,
            10 => 3,
            7 => 4,
            // 4 => 50000,
        ];

        $result = app(FormulaEvaluator::class)
                    ->evaluate(
                        $formula->expression_json,
                        $variables
                    );

        dd($result);
    }
}