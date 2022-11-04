<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Timeline of events
 */
class Timeline extends Vertex implements Archivable
{

    use ArchivableImpl;

}
