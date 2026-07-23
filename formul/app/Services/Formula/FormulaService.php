<?php

namespace App\Services\Formula;

use App\Models\Formula;
use App\Models\Variable;
use App\Models\FormulaDependency;
use Illuminate\Support\Facades\DB;
use App\Services\Condition\ConditionService;
use App\Services\Condition\ConditionTreeService;

class FormulaService
{
    public function __construct(
        protected SyntaxValidator $validator,
        protected ShuntingYard $shuntingYard,
        protected AstBuilder $astBuilder,
        protected DependencyExtractor $dependencyExtractor,
        protected ConditionService $conditionService,
        protected ConditionTreeService $conditionTreeService,
        protected FormulaCreationValidator $formulaCreationValidator,
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
            $expression = ExpressionTreeService::toExpression($formulaAst);
            // extracting the variables which formula depends
            $dependencies = $this->dependencyExtractor->extract($formulaAst);

            $this->formulaCreationValidator->validate(
                windowTypeId: $data['window_type_id'],
                dependencyIds: $dependencies,
                outputVariableId: $data['output_variable_id']
            );
            // create condition

            $conditionAst = null;
            $conditionText = null;

            if (!empty($data['condition_tokens'])) {
                $conditionAst = $this->conditionService->create($data['condition_tokens']);
                $conditionText = $this->conditionTreeService->toExpression($conditionAst);
            }

            try {
                $formula = new Formula();
                $formula->window_type_id = $data['window_type_id'];
                $formula->output_variable_id = $data['output_variable_id'];
                $formula->scop = $data['scop'];
                $formula->expression_json = $formulaAst;
                $formula->condition_json = $conditionAst;
                $formula->condition_text = $conditionText;
                $formula->is_active = true;
                $formula->save();
            } catch (\Throwable $e) {
                dd($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTraceAsString());
            }
            
            $formula->expression_text = "{$formula->resultVariable->code} = {$expression}";
            $formula->load('resultVariable');
            $formula->save();

            $rows = [];
            foreach ($dependencies as $variableId) {
                $variable = Variable::find($variableId);
                $rows[] = [
                    'formula_id' => $formula->id,
                    'variable_id' => $variableId,
                    'type' => $variable->type,
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