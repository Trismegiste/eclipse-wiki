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

        for($i=0; $i<$cardinal; $i++) {
            $props[] = [
                'color' => $colors[$i],
                'isSquare' => (bool) random_int(0, 1),
                'translateX' => random_int(0, 40) - 20,
                'translateY' => random_int(0, 40) - 20,
                'rotate' => random_int(0, 359)
            ];
        }

        return $props;
    }

}
