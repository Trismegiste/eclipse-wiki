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

    protected $metadata = null;

    public function getMetadata(): \stdClass
    {
        if (!is_null($this->metadata)) {
            return $this->metadata;
        }

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

    public function getWidth(): string
    {
        $meta = $this->getMetadata();
        return isset($meta->width) ? $meta->width : 0;
    }

}
