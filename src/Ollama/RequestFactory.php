<?php

/*
 * Eclipse Wiki
 */

namespace App\Ollama;

/**
 * Builds the payload for Ollama server
 */
class RequestFactory
{

    public function __construct(protected string $settingPathname, protected string $llmName)
    {
        
    }

    public function create(string $prompt): ChatPayload
    {
        $req = new ChatPayload($this->llmName);

        $system = new ChatMessage('system');
        $system->content = file_get_contents($this->settingPathname);
        $req->messages[] = $system;

        $question = new ChatMessage('user');
        $question->content = $prompt;
        $req->messages[] = $question;

        return $req;
    }

}
