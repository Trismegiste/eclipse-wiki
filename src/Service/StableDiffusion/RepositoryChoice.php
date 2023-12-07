<?php

/*
 * eclipse-wiki
 */

namespace App\Service\StableDiffusion;

enum RepositoryChoice: string
{
    case local = 'local';
    case remote = 'remote';
}