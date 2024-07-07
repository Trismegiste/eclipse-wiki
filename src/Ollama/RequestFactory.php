<?php

/*
 * Eclipse Wiki
 */

namespace App\Ollama;

/**
 * Description of RequestFacotry
 *
 * @author florent
 */
class RequestFactory
{

    public function __construct(protected string $settingPathname)
    {
        
    }

    public function createBackground(): ChatPayload
    {
        $req = new ChatPayload('mistral');

        $system = new ChatMessage('system');
        $system->content = file_get_contents($this->settingPathname);
        $req->messages[] = $system;

        $question = new ChatMessage('user');
        $question->content = "Fais un historique sur 7 points d'un écumeur qui vit sur une barge. C'est un homme, un technicien, spécialisé dans la réparation de moteurs à fusion. Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille";
        $req->messages[] = $question;

        return $req;
    }

}
