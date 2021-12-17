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
        $resized = imagescale($source, $this->width, -1, IMG_BICUBIC_FIXED);
        imagecopy($target, $resized, 0, 0, 0, 0, $this->width, $this->width);

        return $target;
    }

}
