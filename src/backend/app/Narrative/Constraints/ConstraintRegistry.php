<?php

namespace App\Narrative\Constraints;

use App\Narrative\Values\NarrativeContext;
use Illuminate\Support\Facades\Cache;

class ConstraintRegistry
{
    private array $rules = [];

    public function __construct(array $paths = [])
    {
        foreach ($paths as $path) {
            $this->loadFromPath($path);
        }
    }

    public function loadFromPath(string $path): void
    {
        if (is_dir($path)) {
            $files = glob($path . '/*.yaml');
            foreach ($files as $file) {
                $this->rules = array_merge($this->rules, ConstraintParser::parseYaml($file));
            }
        } elseif (file_exists($path)) {
            $this->rules = array_merge($this->rules, ConstraintParser::parseYaml($path));
        }
    }

    /**
     * @return ConstraintRule[]
     */
    public function applicable(NarrativeContext $ctx): array
    {
        return array_filter(
            $this->rules,
            fn (ConstraintRule $rule) => $rule->matches($ctx)
        );
    }
}
