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
        $bg = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $bg);
        $resized = imagescale($source, $this->width, -1, IMG_GAUSSIAN);
        imagecopy($target, $resized, 0, 0, 0, 0, $this->width, $this->width);

        // economy
        $economy = $npc->economy;
        uasort($economy, function ($a, $b) {
            return $b - $a;
        });
        $economy = array_slice($economy, 0, 3);

        $fg = imagecolorallocate($target, 0x00, 0x00, 0x00);
        $xPos = $this->width / 6;
        foreach ($economy as $key => $val) {
            imagefttext($target, 80, 0, $xPos, 2.0 * $this->height / 3.0, $fg, '/usr/share/fonts/truetype/freefont/FreeSans.ttf', sprintf('%d', $val));
            $xPos += $this->width / 3;
        }

        return $target;
    }

}
