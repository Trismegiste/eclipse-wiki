<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A game hand out for PCs with a GM part
 */
class Handout extends Vertex
{

    public $gmInfo;
    public $target; // a field for PC (not very defined at current time)

}
