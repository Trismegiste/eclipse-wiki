<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

use App\Entity\Vertex;
use App\Service\Ollama\ParameterizedPrompt;

/**
 * Additional informations and behaviors around a LLM-generated content
 */
interface LlmContentInfo
{

    static public function getContentTitle(): string;

    static public function initializeWithVertex(ParameterizedPrompt $param, Vertex $vertex): void;

}
