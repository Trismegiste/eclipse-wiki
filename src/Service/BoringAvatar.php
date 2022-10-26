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
                    'props' => ['size' => 600],
                    'properties' => [
                        ['color' => "red"],
                        ['isSquare' => true, 'color' => "green", 'translateX' => 50, 'translateY' => 30, 'rotate' => 123],
                        ['color' => "cyan", 'translateX' => -10, 'translateY' => 20],
                        ['color' => "black", 'translateX' => 10, 'translateY' => 10, 'rotate' => 200],
                    ]
        ]);

    }

}
