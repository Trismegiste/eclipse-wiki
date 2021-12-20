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
    protected $font = '/usr/share/fonts/truetype/freefont/FreeSans.ttf';

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
        $bg = imagecolorallocate($target, 0xf0, 0xf0, 0xf0);
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
        $size = 80;
        foreach ($economy as $key => $val) {
            $txt = sprintf('%d', $val);
            list($left,, $right,,, ) = imageftbbox($size, 0, $this->font, $txt);
            imagefttext($target, $size, 0, $xPos - ($right - $left) / 2, $this->height * 0.9, $fg, $this->font, $txt);
            $xPos += $this->width / 3;
        }

        // title
        $txt = sprintf('Follow %s', $npc->getTitle());
        list($left,, $right,,, ) = imageftbbox($size, 0, $this->font, $txt);
        $calcSize = $size / ($right - $left) * $this->width * 0.9;
        if ($calcSize > 80) {
            $calcSize = 80;
        }
        list($left,, $right,,, ) = imageftbbox($calcSize, 0, $this->font, $txt);
        imagefttext($target, $calcSize, 0, $this->width / 2 - ($right - $left) / 2, $this->height * 0.65, $fg, $this->font, $txt);

        return $target;
    }

}
