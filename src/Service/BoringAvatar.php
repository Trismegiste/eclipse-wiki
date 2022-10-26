<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Twig\Environment;

/**
 * Port of https://github.com/boringdesigners/boring-avatars
 */
class BoringAvatar
{

    protected $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function createBauhaus(string $name): string
    {
        return $this->twig->render('picture/bauhaus_avatar.svg.twig', [
                    'SIZE' => 80,
                    'props' => ['size' => 200],
                    'properties' => $this->getElementsProperties($name)
        ]);
    }

    protected function getElementsProperties(string $name): array
    {
        $colors = ['red', 'yellow', 'black', 'DeepSkyBlue'];
        $colors = ['#3D1C00', '#86B8B1', '#F2D694', '#FA2A00'];
        $colors = ['#CC0C39', '#C8CF02', '#F8FCC1', '#1693A7'];
        $colors = ['#1A343D', '#FFCC00', '#19ABC2', '#FE4365'];

        $name .= rand(); // to remove - only for test

        $seedChunk = str_split(sha1($name), 8); // (fake) 40 bits of entropy
        $shuffleSeed = array_pop($seedChunk);  // keep the first 8 bits for randomizing colors

        $randomize = str_split(base_convert($shuffleSeed, 16, 2));
        $picked = [];
        while (count($colors)) {
            $choice = array_pop($randomize);
            $value = array_pop($colors);
            if ($choice === '1') {
                $picked[] = $value;
            } else {
                array_unshift($picked, $value);
            }
        }

        $props = [];
        foreach ($seedChunk as $i => $seed) {
            $mask = intval(base_convert($seed, 16, 10)); // we have 32 bits to exploit
            $props[] = [
                'color' => $picked[$i],
                'isSquare' => (bool) ($mask & 1), // 1 bit
                'translateX' => (($mask >> 1) & 63) * 40 / 64 - 20, // 6 bits
                'translateY' => (($mask >> 7) & 63) * 40 / 64 - 20, // 6 bits
                'rotate' => (($mask >> 13) & 511) * 360 / 512 // 9 bits
            ];
        }

        return $props;
    }

}
