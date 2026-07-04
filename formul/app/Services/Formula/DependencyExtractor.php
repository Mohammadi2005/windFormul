<?php

namespace App\Services\Formula;

class DependencyExtractor
{
    public function extract(array $node): array
    {
        $variables = [];

        $this->walk($node, $variables);

        return array_values(array_unique($variables));
    }

    protected function walk(array $node, array &$variables): void
    {
        switch ($node['type']) {

            case 'variable':
                $variables[] = $node['variable_id'];
                return;

            case 'operator':
                $this->walk($node['left'], $variables);
                $this->walk($node['right'], $variables);
                return;

            case 'number':
                return;
        }
    }
}