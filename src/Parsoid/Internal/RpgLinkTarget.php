<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Internal;

use Wikimedia\Parsoid\Core\LinkTarget;
use Wikimedia\Parsoid\Core\LinkTargetTrait;

/**
 * Page title
 */
class RpgLinkTarget implements LinkTarget
{

    use LinkTargetTrait;

    public function createFragmentTarget(string $fragment): LinkTarget
    {
        //  return new self();
    }

    public function getDBkey(): string
    {
        return 'Eclipse Phase';
    }

    public function getFragment(): string
    {
        return '';
    }

    public function getInterwiki(): string
    {
        return '';
    }

    public function getNamespace(): int
    {
        return 0;
    }

}
