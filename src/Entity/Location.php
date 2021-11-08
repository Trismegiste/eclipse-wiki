<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use Trismegiste\Toolbox\MongoDb\Root;
use Trismegiste\Toolbox\MongoDb\RootImpl;

/**
 * Location is a location
 */
class Location implements Root
{

    use RootImpl;

    public $title;
    public $content;

}
