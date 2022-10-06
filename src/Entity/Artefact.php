<?php

/*
 * Eclipse-Wiki
 */

namespace App\Entity;

/**
 * An object, artefact or a MacGuffin of a scenario
 */
class Artefact extends Vertex
{

    protected Vertex $owner;

    public function setOwner(Character|Place $container): void
    {
        $this->owner = $container;
    }

    public function getOwner(): Vertex
    {
        return $this->owner;
    }

}
