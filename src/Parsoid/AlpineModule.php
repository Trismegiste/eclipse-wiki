<?php

/*
 * Eclipse Wiki
 */

namespace App\Parsoid;

use Wikimedia\Parsoid\Ext\ExtensionModule;

/**
 * Module for Parsoid that enriches the DOM with data-* attributes for AlpineJS
 */
class AlpineModule implements ExtensionModule
{

    public function getConfig(): array
    {
        return [
            'name' => 'alpine',
            'domProcessors' => [
                Broadcast::class
            ]
        ];
    }

}
