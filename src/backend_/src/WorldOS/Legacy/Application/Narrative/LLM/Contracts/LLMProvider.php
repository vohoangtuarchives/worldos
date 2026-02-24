<?php

namespace WorldOS\Legacy\Application\Narrative\LLM\Contracts;

interface LLMProvider
{
    /**
     * Generate a response from the LLM.
     *
     * @param string $systemPrompt The context and rules.
     * @param string $userPrompt The specific trigger or stimulus.
     * @return array The parsed JSON response.
     */
    public function generate(string $systemPrompt, string $userPrompt): array;
}
