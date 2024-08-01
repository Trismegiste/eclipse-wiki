<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\Ollama;

/**
 * An entry in the chat
 */
class ChatMessage
{

    public string $content;

    public function __construct(public string $role)
    {
        
    }

}
