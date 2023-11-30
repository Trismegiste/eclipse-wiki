<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid;

use App\Parsoid\Link\PdfOverride;
use Wikimedia\Parsoid\Ext\ExtensionModule;

/**
 * Description of PdfBridge
 *
 * @author trismegiste
 */
class PdfBridge implements ExtensionModule
{

    public function getConfig(): array
    {
        return [
            'name' => 'symfony-bridge',
            'domProcessors' => [
                ['class' => PdfOverride::class, 'args' => [$this->router]]
            ]
        ];
    }

}
