<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Transhuman;

/**
 * Creating avatar for Transhuman
 */
class AvatarMaker
{

    protected $height = 1600;
    protected $width = 900;
    protected $font = __DIR__ . '/OpenSansCondensed-Light.ttf';

    public function getImageChoice(Transhuman $npc): array
    {
        $found = [];
        if (preg_match_all('#\[\[file:([^\]]+)\]\]#', $npc->getContent(), $match)) {
            foreach ($match[1] as $image) {
                $found[$image] = $image;
            }
        }

        return $found;
    }

    public function generate(Transhuman $npc, string $image)
    {
        if (!file_exists($image)) {
            throw new \RuntimeException("$image does not exist");
        }
        $source = imagecreatefromstring(file_get_contents($image));
        $target = imagecreatetruecolor($this->width, $this->height);
        $bg = imagecolorallocate($target, 0xf7, 0xf7, 0xf7);
        imagefill($target, 0, 0, $bg);
        $resized = imagescale($source, $this->width, -1, IMG_GAUSSIAN);
        imagecopy($target, $resized, 0, 0, 0, 0, $this->width, $this->width);

        $size = 80;
        $fg = imagecolorallocate($target, 0x00, 0x00, 0x00);

        // title
        $txt = sprintf('Follow %s', $npc->getTitle());
        list($left,, $right,,, ) = imageftbbox($size, 0, $this->font, $txt);
        $calcSize = $size / ($right - $left) * $this->width * 0.9;
        if ($calcSize > 80) {
            $calcSize = 80;
        }
        list($left,, $right,,, ) = imageftbbox($calcSize, 0, $this->font, $txt);
        imagefttext($target, $calcSize, 0, $this->width / 2 - ($right - $left) / 2, $this->height * 0.7, $fg, $this->font, $txt);

        // economy
        $economy = $npc->economy;
        array_shift($economy);
        uasort($economy, function ($a, $b) {
            return $b - $a;
        });
        $economy = array_slice($economy, 0, 3);

        $txtPos = $this->width / 6;
        $imgPos = $this->width / 24;
        $size = 40;
        foreach ($economy as $key => $val) {
            // text
            $txt = $this->printFollowers(10 ** ($val - random_int(15, 75) / 100.0));
            list($left,, $right,,, ) = imageftbbox($size, 0, $this->font, $txt);
            imagefttext($target, $size, 0, $txtPos - ($right - $left) / 2, $this->height * 0.97, $fg, $this->font, $txt);
            $txtPos += $this->width / 3;

            // icon
            $socnet = imagecreatefromstring(file_get_contents('/home/flo/Develop/eclipse-wiki/public/socnet/' . $key . '.png'));
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
