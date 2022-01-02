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

    public $player;
    public $drama;
    public $roll1;
    public $roll2;
    public $roll3;
    public $resolution = [];
    public $pcChoice = [];

}
