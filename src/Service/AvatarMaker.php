<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Transhuman;
use GdImage;

/**
 * Creating avatar for Transhuman
 */
class AvatarMaker
{

    protected $height;
    protected $width;
    protected $font = __DIR__ . '/OpenSansCondensed-Light.ttf';
    protected $socNetFolder;

    public function __construct(string $socNetFolder, int $width = 503, int $height = 894)
    {
        $this->height = $height;
        $this->width = $width;
        $this->socNetFolder = $socNetFolder;
    }

    /**
     * Create the profile pic
     * @param Transhuman $npc
     * @param GdImage $source the GD resource of an avatar picture
     * @return resource the GD2 image resource
     * @throws \RuntimeException
     */
    public function generate(Transhuman $npc, \GdImage $source)
    {
        $target = imagecreatetruecolor($this->width, $this->height);
        $bg = imagecolorallocate($target, 0xf0, 0xf0, 0xf0);
        imagefill($target, 0, 0, $bg);
        imagecopy($target, $source, 0, 0, 0, 0, $this->width, $this->width);

        $size = 80;
        $fg = imagecolorallocate($target, 0x00, 0x00, 0x00);

        // title
        $txt = sprintf('Follow %s', $npc->getTitle());
        list($left,, $right,,, ) = \imageftbbox($size, 0, $this->font, $txt);
        $calcSize = $size / ($right - $left) * $this->width * 0.9;
        if ($calcSize > 100) {
            $calcSize = 100;
        }
        list($left,, $right,,, ) = \imageftbbox($calcSize, 0, $this->font, $txt);
        imagefttext($target, $calcSize, 0, $this->width / 2 - ($right - $left) / 2, $this->height * 0.7, $fg, $this->font, $txt);

        // economy
        $economy = array_filter($npc->economy, function ($val, $key) {
            if ($key === 'Ressource') {
                return false;
            }
            return !empty($val);
        }, ARRAY_FILTER_USE_BOTH);
        uasort($economy, function ($a, $b) {
            return $b - $a;
        });
        $economy = array_slice($economy, 0, 3);

        $txtPos = $this->width / 6;
        $imgPos = $this->width / 24;
        $size = 32;
        foreach ($economy as $key => $val) {
            // text
            $txt = $this->printFollowers(10 ** ($val - random_int(10, 90) / 100.0));
            list($left,, $right,,, ) = imageftbbox($size, 0, $this->font, $txt);
            imagefttext($target, $size, 0, $txtPos - ($right - $left) / 2, $this->height * 0.97, $fg, $this->font, $txt);
            $txtPos += $this->width / 3;

            // icon
            $socnet = imagecreatefromstring(file_get_contents(join_paths($this->socNetFolder, $key . '.png')));
            $resized = imagescale($socnet, $this->width / 4, -1, IMG_GAUSSIAN);
            imagecopy($target, $resized, $imgPos, $this->height * 0.78, 0, 0, $this->width / 4, $this->width / 4);
            $imgPos += $this->width / 3;
        }

        return $target;
    }

    const coeff = ['', 'k', 'M', 'G', 'T', 'P'];

    private function printFollowers(int $num): string
    {
        $multiplier = (int) floor(log10($num) / 3);

        return sprintf($multiplier !== 0 ? '%.1f%s' : '%d', \round($num / (10 ** (3 * $multiplier)), 2), self::coeff[$multiplier]);
    }

}
