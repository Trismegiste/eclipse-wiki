<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\Ollama;

/**
 * Payload to send to OLlama API
 */
class ChatPayload
{

    public array $messages = [];
    public bool $stream = true;

    public function __construct(public string $model)
    {
        
    }

}
