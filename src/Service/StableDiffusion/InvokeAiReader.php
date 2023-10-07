<?php

/*
 * Bronze
 */

namespace App\Service\StableDiffusion;

/**
 * InvokeAI png reader format
 */
class InvokeAiReader extends PngReader
{

    const INVOKEAI_KEY = 'invokeai_metadata';

    public function getMetadata(): \stdClass
    {
        $dump = $this->getTextChunk();
        if (!key_exists(self::INVOKEAI_KEY, $dump)) {
            return new \stdClass();
        }

        return json_decode($dump[self::INVOKEAI_KEY]);
    }

    public function getPositivePrompt(): string
    {
        $meta = $this->getMetadata();
        return isset($meta->positive_prompt) ? $meta->positive_prompt : '';
    }

}
