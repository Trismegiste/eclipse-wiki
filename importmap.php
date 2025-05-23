<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => 'app.js',
        'entrypoint' => true,
    ],
    'battlemap-factory' => [
        'path' => 'place/battlemap-factory.js',
    ],
    'battlemap-loader' => [
        'path' => 'place/battlemap-loader.js',
    ],
    'battlemap-builder' => [
        'path' => 'place/BattlemapBuilder.js',
    ],
    'battlemap-document' => [
        'path' => 'place/BattlemapDocument.js',
    ],
    'battlemap-editor' => [
        'path' => 'place/BattlemapEditor.js',
    ],
    'legend-highlight-subscriber' => [
        'path' => 'place/legend-highlight-subscriber.js',
    ],
    'cubemap-viewer' => [
        'path' => 'place/cubemap-viewer.js',
    ],
    'dice-roller' => [
        'path' => 'DicePool.js',
    ],
    'picture-broadcasting' => [
        'path' => 'picture-pusher.js',
    ],
    'quote-broadcasting' => [
        'path' => 'quote-pusher.js',
    ],
    'wikitext' => [
        'path' => 'wikitext-autocomplete.js',
    ],
    'timeline-tree' => [
        'path' => 'timeline/tree.js',
    ],
    'wikitree' => [
        'path' => 'timeline/wikitree-editable.js',
    ],
    'selector-detailed' => [
        'path' => 'selector-with-detail.js',
    ],
    'quick-npc' => [
        'path' => 'npc-graph-creation.js',
    ],
    'autofocus' => [
        'path' => 'autofocus.js',
    ],
    'sd-gallery-search' => [
        'path' => 'sd-gallery-search.js',
    ],
    'ollama-client' => [
        'path' => 'ollama-client.js',
    ],
    'avatar-type' => [
        'path' => 'avatar-type.js',
    ],
    'cropper-utils' => [
        'path' => 'crop-helper.js',
    ],
    'mercure-client' => [
        'path' => 'mercure-client.js',
    ],
    'babylonjs' => [
        'version' => '6.29.2',
    ],
    'meshwriter' => [
        'version' => '1.3.2',
    ],
    'babylonjs-gui' => [
        'version' => '6.29.2',
    ],
    'mousetrap' => [
        'version' => '1.6.5',
    ],
    'howler' => [
        'version' => '2.2.4',
    ],
    'qrious' => [
        'version' => '4.0.2',
    ],
    'js-autocomplete' => [
        'version' => '1.0.4',
    ],
    'caret-pos' => [
        'version' => '2.0.0',
    ],
    'alpinejs' => [
        'version' => '3.13.3',
    ],
    'croppie' => [
        'version' => '2.6.5',
    ],
    '@multiavatar/multiavatar' => [
        'version' => '1.0.7',
    ],
    'swiper/swiper-bundle' => [
        'version' => '11.0.5',
    ],
    'swiper/modules/manipulation' => [
        'version' => '11.0.5',
    ],
    'ollama/browser' => [
        'version' => '0.5.7',
    ],
    'dramatron/scenario' => [
        'path' => 'dramatron/Scenario.js',
    ],
    'dramatron/extract-character-schema' => [
        'path' => 'dramatron/extract-character-schema.js',
    ],
    'dramatron/extract-place-schema' => [
        'path' => 'dramatron/extract-place-schema.js',
    ],
    'dramatron/spa' => [
        'path' => 'dramatron/spa.js',
    ]
];
