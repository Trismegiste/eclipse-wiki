<?php

/**
 * Returns the import map for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "preload" set to true for any modules that are loaded on the initial
 *     page load to help the browser download them earlier.
 *
 * The "importmap:require" command can be used to add new entries to this file.
 *
 * This file has been auto-generated by the importmap commands.
 */
return [
    'app' => [
        'path' => 'app.js',
        'preload' => true,
    ],
    'battlemap-loader' => [
        'path' => 'battlemap-loader.js',
    ],
    'battlemap-builder' => [
        'path' => 'BattlemapBuilder.js',
    ],
    'battlemap-document' => [
        'path' => 'BattlemapDocument.js',
    ],
    'dice-roller' => [
        'path' => 'DicePool.js',
    ],
    'alpinejs' => [
        'downloaded_to' => 'vendor/alpinejs.js',
        'url' => 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.2/+esm',
    ],
    'babylonjs' => [
        'downloaded_to' => 'vendor/babylonjs.js',
        'url' => 'https://cdn.jsdelivr.net/npm/babylonjs@6.29.2/+esm',
    ],
    'meshwriter' => [
        'downloaded_to' => 'vendor/meshwriter.js',
        'url' => 'https://cdn.jsdelivr.net/npm/meshwriter@1.3.2/+esm',
    ],
    'babylonjs-gui' => [
        'downloaded_to' => 'vendor/babylonjs-gui.js',
        'url' => 'https://cdn.jsdelivr.net/npm/babylonjs-gui@6.29.2/+esm',
    ],
    'mousetrap' => [
        'downloaded_to' => 'vendor/mousetrap.js',
        'url' => 'https://cdn.jsdelivr.net/npm/mousetrap@1.6.5/+esm',
    ],
];