<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Twig\Environment;

/**
 * Port of https://github.com/boringdesigners/boring-avatars
 * Changes : far better use of the seed
 */
class BoringAvatar
{

    const defaultPixelSize = 250;

    protected $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function createBauhaus(string $name): string
    {
        return $this->twig->render('picture/bauhaus_avatar.svg.twig', [
                    'SIZE' => 80,
                    'props' => ['size' => self::defaultPixelSize],
                    'properties' => $this->getElementsProperties($name)
        ]);
    }

    protected function getElementsProperties(string $name): array
    {
        $seedChunk = str_split(sha1($name), 8); // 160 bits (= 40 hexadecimal characters) of (fake) entropy splitted in 5
        $shuffleSeed = array_pop($seedChunk);  // keep the first 32 bits for randomizing colors
        $picked = $this->getPalette($shuffleSeed);

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

    protected function getPalette(string $seed): array
    {
        if (strlen($seed) !== 8) {
            throw new \InvalidArgumentException();
        }

        $palette = [
            ['red', 'yellow', 'black', 'DeepSkyBlue'],
            ['#3D1C00', '#86B8B1', '#F2D694', '#FA2A00'],
            ['#CC0C39', '#C8CF02', '#F8FCC1', '#1693A7'],
            ['#1A343D', '#FFCC00', '#19ABC2', '#FE4365']
        ];

        $randomize = intval(base_convert($seed, 16, 10));
        $colors = $palette[$randomize & 3];
        $randomize = str_split(base_convert($randomize >> 2, 10, 2));
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

        return $picked;
    }

}
