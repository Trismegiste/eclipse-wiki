<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\Ollama;

/**
 * Payload to send to OLlama API
 * https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-chat-completion
 */
class ChatPayload
{

    public array $messages = [];
    public bool $stream = true;
    public array $options = [];

    public function __construct(public string $model, float $temperature)
    {
        $this->options['temperature'] = $temperature;
        $this->options['num_ctx'] = 4096;
    }

}
