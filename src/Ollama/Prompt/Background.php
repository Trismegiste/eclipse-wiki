<?php

/*
 * eclipse-wiki
 */

namespace App\Ollama\Prompt;

/**
 * Description of Background
 *
 * @author trismegiste
 */
class Background implements \Stringable
{

    public string $role;
    public string $location;
    public string $gender;
    public string $job;
    public string $speciality;

    public function __toString()
    {
        return
                "Fais un historique sur 7 points d'{$this->role} qui vit sur {$this->location}. " .
                "C'est {$this->gender}, {$this->job}, spécialisé dans {$this->speciality}. " .
                "Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille";
    }

}
