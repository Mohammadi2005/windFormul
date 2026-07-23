<?php

namespace App\Services\Formula;

use Exception;
use App\Models\FormulaDependency;
use Illuminate\Support\Collection;
use App\Models\Formula;

class FormulaExecutionPlanner
{
    /**
     * تعیین ترتیب اجرای فرمول‌ها برای هر سکشن
     */
    public function plan(array $selectedSections,Collection $dependencies): array
    {
        foreach ($selectedSections as &$section) {

            $section['formulas'] = $this->sort(
                $section['formulas'], $dependencies
            );

        }

        return $selectedSections;
    }

    /**
     * مرتب سازی فرمول ها بر اساس وابستگی
     */
    protected function sort(array $formulas,Collection $dependencies): array
    {
        if (count($formulas) <= 1) {
            return $formulas;
        }


        /*
         * Map خروجی هر متغیر => فرمول تولید کننده
         */
        $outputMap = [];

        foreach ($formulas as $formula) {

            $outputMap[$formula->output_variable_id] = $formula;

        }



        /*
         * DFS State
         */
        $visited = [];

        $visiting = [];

        $ordered = [];



        foreach ($formulas as $formula) {

            $this->visit(
                $formula,
                $dependencies,
                $outputMap,
                $visited,
                $visiting,
                $ordered
            );

        }

        return $ordered;
    }



    /**
     * DFS
     */
    protected function visit(
        Formula $formula,
        Collection $dependencies,
        array $outputMap,
        array &$visited,
        array &$visiting,
        array &$ordered
    ): void {

        /*
         * قبلاً بررسی شده
         */
        if (isset($visited[$formula->id])) {
            return;
        }



        /*
         * وابستگی دوری
         */
        if (isset($visiting[$formula->id])) {

            throw new Exception(
                "Circular dependency detected at Formula {$formula->id}"
            );

        }



        /*
         * در حال بررسی
         */
        $visiting[$formula->id] = true;



        /*
         * وابستگی های این فرمول
         */
        foreach ($dependencies[$formula->id] ?? [] as $dependency) {

            $variableId = $dependency->variable_id;

            /*
             * اگر این متغیر خروجی یک فرمول دیگر است
             */
            if (!isset($outputMap[$variableId])) {
                continue;
            }

            $this->visit(
                $outputMap[$variableId],
                $dependencies,
                $outputMap,
                $visited,
                $visiting,
                $ordered
            );

        }



        /*
         * پایان بررسی
         */
        unset($visiting[$formula->id]);

        $visited[$formula->id] = true;

        $ordered[] = $formula;
    }
}