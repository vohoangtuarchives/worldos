<?php

namespace WorldOS\Legacy\Infrastructure\AI;

interface LanguageModelAdapter
{
    /**
     * @param string $systemPrompt
     * @param string $userPrompt
     * @return string Generated narrative content
     */
    public function generate(string $systemPrompt, string $userPrompt): string;
}
