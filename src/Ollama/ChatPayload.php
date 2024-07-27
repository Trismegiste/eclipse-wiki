<?php

/*
 * Eclipse Wiki
 */

namespace App\Ollama;

/**
 * Payload to send to OLlama API
 */
class ChatPayload
{

    public array $messages = [];
    public bool $stream = true;
    public array $options = [];

    public function __construct(public string $model, int $contextToken)
    {
        $this->options['num_ctx'] = $contextToken;
    }

}
