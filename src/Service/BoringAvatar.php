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
                    'properties' => $this->getElementsProperties($name, 4)
        ]);

    }

    protected function getElementsProperties(string $name, int $cardinal): array
    {
        $colors = ['red', 'yellow', 'DeepSkyBlue', 'black'];
        shuffle($colors);
        $props = [];

        foreach(str_split(sha1($name), 10) as $i => $seed) {
            $mask = intval(base_convert($seed, 16, 10)); // we have 40 bits to exploit
            $props[] = [
                'color' => $colors[$i],
                'isSquare' => (bool) ($mask & 1),
                'translateX' => (($mask >> 1) & 63) * 40 / 64 - 20,
                'translateY' => (($mask >> 7) & 63) * 40 / 64 - 20,
                'rotate' => ($mask >> 13) % 360
            ];
        }

        return $props;
    }

}
