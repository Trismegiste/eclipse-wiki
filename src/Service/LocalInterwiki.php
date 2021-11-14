<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

/**
 * interwiki namespaces for this app
 */
class LocalInterwiki implements \Mike42\Wikitext\InterwikiRepository
{

    protected $interwiki;

    public function __construct(string $host)
    {
        $this->interwiki = [
            'ep' => 'https://' . $host . '/fr/wiki/$1',
            'pnj' => '/npc/wiki/$1'
        ];
    }

    public function getTargetUrl(string $namespace): string
    {
        return $this->interwiki[$namespace];
    }

    public function hasNamespace(string $ns): bool
    {
        return array_key_exists($ns, $this->interwiki);
    }

}
