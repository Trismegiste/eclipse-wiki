<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid;

use App\Parsoid\Link\PdfOverride;
use App\Parsoid\TagHandler\Carrousel;
use App\Parsoid\TagHandler\MorphBank;
use App\Parsoid\TagHandler\PushPublic;
use App\Service\Storage;
use Wikimedia\Parsoid\Ext\ExtensionModule;

/**
 * Extension for PDF output
 */
class PdfBridge implements ExtensionModule
{

    public function __construct(protected Storage $storage)
    {
        
    }

    public function getConfig(): array
    {
        return [
            'name' => 'symfony-bridge',
            'domProcessors' => [
                ['class' => PdfOverride::class, 'args' => [$this->storage]]
            ],
            'tags' => [
                ['name' => 'carrousel', 'handler' => Carrousel::class],
                ['name' => 'morphbank', 'handler' => MorphBank::class],
                ['name' => 'pushpublic', 'handler' => PushPublic::class],
            ]
        ];
    }

}
