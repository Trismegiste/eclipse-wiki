<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid;

use App\Parsoid\Link\PdfOverride;
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
                ['name' => 'carrousel', 'handler' => CarrouselTagHandler::class],
                ['name' => 'morphbank', 'handler' => MorphBankTagHandler::class]
            ]
        ];
    }

}
