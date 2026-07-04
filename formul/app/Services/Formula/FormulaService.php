<?php

namespace App\Services\Formula;

use App\Models\Formula;
use App\Models\FormulaDependency;
use Illuminate\Support\Facades\DB;

class FormulaService
{
    public function __construct(
        protected FormulaValidator $validator,
        protected ShuntingYard $shuntingYard,
        protected AstBuilder $astBuilder,
        protected DependencyExtractor $dependencyExtractor,
    ) {
    }

    public function create(array $data): Formula
    {
        return DB::transaction(function () use ($data) {
            
            // اعتبارسنجی Token ها
            $this->validator->validate($data['tokens']);

            // Infix -> Postfix
            $postfix = $this->shuntingYard->convert($data['tokens']);
            
            // Postfix -> AST
            $ast = $this->astBuilder->build($postfix);
            // dd($ast);

            $dependencies = $this->dependencyExtractor->extract($ast);

            $formula = Formula::create([
                // 'name' => $data['name'],
                // 'code' => $data['code'],
                'window_type_id' => $data['window_type_id'],
                'output_variable_id' => $data['output_variable_id'],
                'expression_json' => $ast,
                'execution_order' => $data['execution_order'],
                'is_active' => true,
            ]);
            $rows = [];

            foreach ($dependencies as $variableId) {

                $rows[] = [
                    'formula_id' => $formula->id,
                    'variable_id' => $variableId,
                    'type' => 'input',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            }

            FormulaDependency::insert($rows);


            return $formula;
        });
    }
}