<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Ollama;

/**
 * Entity used in prompt generation
 */
class ParameterizedPrompt
{

    public array $param = [];
    public string $prompt;

}
