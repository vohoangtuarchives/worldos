<?php

namespace App\Narrative\Constraints;

use Symfony\Component\Yaml\Yaml;

class ConstraintParser
{
    /**
     * @return ConstraintRule[]
     */
    public static function parseYaml(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $data = Yaml::parseFile($path);
        $rules = [];

        if (isset($data['constraints'])) {
            foreach ($data['constraints'] as $ruleData) {
                $rules[] = new ConstraintRule(
                    id: $ruleData['id'],
                    when: $ruleData['when'] ?? [],
                    forbid: $ruleData['forbid'] ?? [],
                    reason: $ruleData['reason']
                );
            }
        }

        return $rules;
    }
}
