<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use Trismegiste\Strangelove\MongoDb\Root;
use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * A synopsis is a draft for a future set of Timeline + Scene + Transhuman + Place
 * It's a sandbox heavily driven by LLM output
 * This entity is mostly a bunch a strings
 */
class Synopsis extends Vertex
{

    public string $pitch;
    public string $story;
    public array $act = [];
    public array $character = [];
    public array $place = [];

}
