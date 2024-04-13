<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use App\Parsoid\Link\BrowserOverride;
use App\Parsoid\TagHandler\Carrousel;
use App\Parsoid\TagHandler\MorphBank;
use App\Parsoid\TagHandler\PushPublic;
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
            'name' => 'symfony-bridge',
            'domProcessors' => [
                ['class' => BrowserOverride::class, 'args' => [$this->router]]
            ],
            'tags' => [
                ['name' => 'carrousel', 'handler' => Carrousel::class],
                ['name' => 'morphbank', 'handler' => MorphBank::class],
                ['name' => 'pushpublic', 'handler' => PushPublic::class],
            ]
        ];
    }

}
