<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Transhuman;
use GDText\Box;
use GDText\Color;
use GDText\Enum\HorizontalAlignment;
use GDText\Enum\VerticalAlignment;
use SplFileInfo;
use Twig\Environment;
use function join_paths;

/**
 * Creating avatar for Transhuman
 */
class AvatarMaker
{

    const fontSubDir = 'designfonts';
    const iconSubDir = 'socnet';

    protected int $leftPadding = 15;
    protected readonly int $paddedWidth;
    protected readonly Color $gray;
    protected readonly Color $black;

    public function __construct(protected Environment $twig, protected string $publicFolder, protected int $width = 503, protected int $height = 894)
    {
        $this->paddedWidth = $this->width - 2 * $this->leftPadding;
        $this->gray = new Color(0x70, 0x70, 0x70);
        $this->black = new Color(0, 0, 0);
    }

    /**
     * Create the profile pic
     * @param Transhuman $npc
     * @param SplFileInfo $source
     * @return SplFileInfo
     */
    public function generate(Transhuman $npc, SplFileInfo $source): SplFileInfo
    {
        // social network profile
        $top = 0;
        $profile = imagecreatetruecolor($this->width, $this->height);
        $bg = imagecolorallocate($profile, 0xf0, 0xf0, 0xf0);
        imagefill($profile, 0, 0, $bg);
        // profile pic
        $token = imagecreatefrompng($source->getPathname());
        imagecopy($profile, $token, 0, $top, 0, 0, $this->width, $this->width);

        // name
        $top += $this->width + 10;
        $box = new Box($profile);
        $box->setFontFace($this->getFontPath('OpenSansCondensed-Light'))
                ->setFontColor($this->black)
                ->setFontSize(50)
                ->setBox($this->leftPadding, $top, (int) ($this->paddedWidth * 0.7), 65)
                ->setTextAlign(HorizontalAlignment::Left, VerticalAlignment::Top)
                ->draw($npc->getTitle());

        // button follow
        $button = imagecreatefrompng(join_paths($this->publicFolder, self::iconSubDir, 'button_follow.png'));
        imagecopy($profile, $button, 358, $top + 15, 0, 0, 130, 40);

        // hashtags
        $top += 70;
        if (!empty($npc->hashtag)) {
            $box = new Box($profile);
            $box->setFontFace($this->getFontPath('DejaVuSans'))
                    ->setFontColor($this->gray)
                    ->setFontSize(24)
                    ->setBox($this->leftPadding, $top, $this->paddedWidth, 85)
                    ->setTextAlign(HorizontalAlignment::Left, VerticalAlignment::Center)
                    ->draw($npc->hashtag);
        }

        // socnet icons
        $top += 110;
        $imgPos = (int) ($this->width / 24);
        $iconSize = (int) ($this->width / 4);
        foreach ($this->filterSocNet($npc) as $key => $level) {
            $icon = imagecreatefrompng(join_paths($this->publicFolder, self::iconSubDir, $key . '.png'));
            $resized = imagescale($icon, $iconSize, -1, IMG_BICUBIC_FIXED);
            imagecopy($profile, $resized, $imgPos, $top, 0, 0, $iconSize, $iconSize);
            $imgPos += (int) ($this->width / 3);
        }

        // socnet followers
        $top += $iconSize + 10;
        $txtPos = (int) ($this->width / 24);
        foreach ($this->filterSocNet($npc) as $level) {
            (new Box($profile))
                    ->setFontFace($this->getFontPath('OpenSansCondensed-Light'))
                    ->setFontColor($this->black)
                    ->setFontSize(40)
                    ->setBox($txtPos, $top, $iconSize, 40)
                    ->setTextAlign(HorizontalAlignment::Center, VerticalAlignment::Top)
                    ->draw($this->printFollowers(10 ** ($level - random_int(10, 75) / 100.0)));

            $txtPos += (int) ($this->width / 3);
        }

        // write
        $pngTarget = sys_get_temp_dir() . '/profile-' . $npc->getTitle() . '.png';
        imagepng($profile, $pngTarget);

        return new SplFileInfo($pngTarget);
    }

    protected function filterSocNet(Transhuman $npc): array
    {
        $economy = array_filter($npc->economy, function ($val, $key) {
            if ($key === 'Ressource') {
                return false;
            }
            return !empty($val);
        }, ARRAY_FILTER_USE_BOTH);
        uasort($economy, function ($a, $b) {
            return $b - $a;
        });

        return array_slice($economy, 0, 3);
    }

    const coeff = ['', 'k', 'M', 'G', 'T', 'P'];

    private function printFollowers(float $num): string
    {
        $multiplier = (int) floor(log10($num) / 3);

        return sprintf($multiplier !== 0 ? '%.1f%s' : '%d', \round($num / (10 ** (3 * $multiplier)), 2), self::coeff[$multiplier]);
    }

    protected function getFontPath(string $name): string
    {
        return join_paths($this->publicFolder, self::fontSubDir, $name . '.ttf');
    }

}
