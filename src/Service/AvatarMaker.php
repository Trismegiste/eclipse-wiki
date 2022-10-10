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
    protected $publicFolder;
    protected $twig;

    public function __construct(\Twig\Environment $twig, string $publicFolder, int $width = 503, int $height = 894)
    {
        $this->height = $height;
        $this->width = $width;
        $this->publicFolder = $publicFolder;
        $this->twig = $twig;
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
        imagesavealpha($source, true);
        ob_start();
        imagepng($source);
        $profilePic = base64_encode(ob_get_clean());

        $socnet = array_map(function (int $val) {
            return $this->printFollowers(10 ** ($val - random_int(10, 90) / 100.0));
        }, $this->filterSocNet($npc));

        $html = $this->twig->render('picture/wk_profile.html.twig', [
            'width' => $this->width,
            'profile_pic' => $profilePic,
            'npc' => $npc,
            'font' => base64_encode(file_get_contents($this->font)),
            'socnet' => $socnet,
            'localbase' => $this->publicFolder
        ]);

        // extensions are important for wkhtmltopng
        $basename = "target" . rand();
        $htmlTarget = sys_get_temp_dir() . '/' . $basename . '.html';
        $pngTarget = sys_get_temp_dir() . '/' . $basename . '.png';
        file_put_contents($htmlTarget, $html);
        $matrixing = new \Symfony\Component\Process\Process([
            'wkhtmltoimage',
            '--width', $this->width,
            '--height', $this->height,
            '--enable-local-file-access',
            $htmlTarget,
            $pngTarget
        ]);
        $matrixing->mustRun();

        return imagecreatefrompng($pngTarget);
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
