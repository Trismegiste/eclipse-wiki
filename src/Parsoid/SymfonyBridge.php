<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\Ext\ExtensionModule;

/**
 * Module for Parsoid that bridges with Symfony
 */
class SymfonyBridge implements ExtensionModule
{

    public function __construct(protected UrlGeneratorInterface $router)
    {
        
    }

    public function getConfig(): array
    {
        return [
            'name' => 'symfony',
            'domProcessors' => [
                ['class' => LinkOverride::class, 'args' => [$this->router]]
            ]
        ];
    }

}
