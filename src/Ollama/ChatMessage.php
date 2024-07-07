<?php

/*
 * Eclipse Wiki
 */

namespace App\Ollama;

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
