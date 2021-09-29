<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Service\MediaWiki;

/**
 * Description of TraitProvider
 *
 * @author flo
 */
class TraitProvider
{

    protected $wiki;

    public function __construct(MediaWiki $param)
    {
        $this->wiki = $param;
    }

}
