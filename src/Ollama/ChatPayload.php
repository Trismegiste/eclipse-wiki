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
    public bool $stream = false;

    public function __construct(public string $model)
    {
        
    }

}
