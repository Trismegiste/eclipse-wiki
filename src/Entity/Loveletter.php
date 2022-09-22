<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Loveletter
 */
class Loveletter extends Vertex
{

    public string $player;
    public string $drama;
    public $roll1;
    public $roll2;
    public $roll3;
    public array $resolution = [];
    public array $pcChoice = [];

}
