<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extensions for AlpineJs
 */
class AlpineJsExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('alpinejs_flashes', [$this, 'flattenFlashBag'])
        ];
    }

    public function flattenFlashBag(array $bag): array
    {
        $flat = [];

        foreach ($bag as $level => $listing) {
            foreach ($listing as $flash) {
                $flat[] = ['level' => $level, 'message' => $flash];
            }
        }

        return ['flashes' => $flat];
    }

}
