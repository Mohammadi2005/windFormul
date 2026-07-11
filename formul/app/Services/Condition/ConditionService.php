<?php 

namespace App\Services\Condition;

class ConditionService
{
    public function __construct(
        protected ConditionSyntaxValidator $validator,
        protected ConditionShuntingYard $shuntingYard,
        protected ConditionAstBuilder $astBuilder,
    ) {
    }

    public function create(array $tokens): array
    {
        $this->validator->validate($tokens);

        $postfix = $this->shuntingYard->convert($tokens);

        $ast = $this->astBuilder->build($postfix);
        
        return $ast;
    }
}