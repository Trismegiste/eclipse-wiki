<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Transhuman;
use GdImage;
use GDText\Box;
use GDText\Color;
use GDText\Enum\HorizontalAlignment;
use GDText\Enum\VerticalAlignment;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Twig\Environment;

/**
 * Creating avatar for Transhuman
 */
class AvatarMaker
{

    protected int $leftMargin = 20;
    protected int $rightMargin;

    public function __construct(protected Environment $twig, protected string $publicFolder, protected int $width = 503, protected int $height = 894)
    {
        $this->rightMargin = $this->width - $this->leftMargin;
    }

    /**
     * Create the profile pic
     * @param Transhuman $npc
     * @param GdImage $source the GD resource of an avatar picture
     * @return resource the GD2 image resource
     * @throws RuntimeException
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
        $box->setFontFace($this->publicFolder . '/designfonts/OpenSansCondensed-Light.ttf')
                ->setFontColor(new Color(0, 0, 0));
        $box->setFontSize(50);
        $box->setBox($this->leftMargin, $top, 460, 460);
        $box->setTextAlign(HorizontalAlignment::Left, VerticalAlignment::Top);
        $box->draw($npc->getTitle());

        // hashtags
        $top += 65;
        $box = new Box($profile);
        $box->setFontFace('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf')
                ->setFontColor(new Color(0x70, 0x70, 0x70));
        $box->setFontSize(24);
        $box->setBox($this->leftMargin, $top, $this->rightMargin, $top + 72);
        $box->setTextAlign(HorizontalAlignment::Left, VerticalAlignment::Top);
        $box->draw($npc->hashtag);

        $pngTarget = sys_get_temp_dir() . '/profile-' . $npc->getTitle() . '.png';
        imagepng($profile, $pngTarget);

        return new SplFileInfo($pngTarget);

        $profilePic = base64_encode(file_get_contents($source->getPathname()));

        $socnet = array_map(function (int $val) {
            return $this->printFollowers(10 ** ($val - random_int(10, 90) / 100.0));
        }, $this->filterSocNet($npc));

        $html = $this->twig->render('picture/wk_profile.html.twig', [
            'width' => $this->width,
            'profile_pic' => $profilePic,
            'npc' => $npc,
            'socnet' => $socnet,
            'localbase' => $this->publicFolder
        ]);

        // extensions are important for wkhtmltopng
        $basename = "target" . rand();
        $htmlTarget = sys_get_temp_dir() . '/' . $basename . '.html';
        $pngTarget = sys_get_temp_dir() . '/' . $basename . '.png';
        file_put_contents($htmlTarget, $html);
        $matrixing = new Process([
            'wkhtmltoimage',
            '--width', $this->width,
            '--height', $this->height,
            '--enable-local-file-access',
            $htmlTarget,
            $pngTarget
        ]);
        $matrixing->mustRun();

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

}
