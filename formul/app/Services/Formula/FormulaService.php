<?php

namespace App\Services\Formula;

use App\Models\Formula;
use App\Models\FormulaDependency;
use Illuminate\Support\Facades\DB;
use App\Services\Condition\ConditionService;

class FormulaService
{
    public function __construct(
        protected SyntaxValidator $validator,
        protected ShuntingYard $shuntingYard,
        protected AstBuilder $astBuilder,
        protected DependencyExtractor $dependencyExtractor,
        protected ConditionService $conditionService,
    ) {
    }

    public function create(array $data): Formula
    {
        return DB::transaction(function () use ($data) {

            // Syntax Validator for token 
            $this->validator->validate($data['formula_tokens']);

            // Infix -> Postfix
            $postfix = $this->shuntingYard->convert($data['formula_tokens']);
            
            // Postfix -> AST
            $formulaAst = $this->astBuilder->build($postfix);

            // extracting the variables which formula depends
            $dependencies = $this->dependencyExtractor->extract($formulaAst);



            // create condition
            $conditionAst = $this->conditionService->create($data['condition_tokens']);

            try {

                $formula = new Formula();

                $formula->window_type_id = $data['window_type_id'];
                $formula->output_variable_id = $data['output_variable_id'];
                $formula->execution_order = $data['execution_order'];
                $formula->scop = $data['scop'];
                $formula->expression_json = $formulaAst;
                $formula->condition_json = $conditionAst;
                $formula->is_active = true;

                $formula->save();


            } catch (\Throwable $e) {

                dd(
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                );
            }

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

            // dd($rows);
            return $formula;
        });
    }
}