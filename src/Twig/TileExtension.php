<?php

/*
 * Eclipse-Wiki
 */

namespace App\Twig;

use App\Repository\TileProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * SVG Tile renderer for embedding in twig
 */
class TileExtension extends AbstractExtension
{

    protected $provider;

    public function __construct(TileProvider $repo)
    {
        $this->provider = $repo;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_tile', [$this, 'renderTileSvg'], ['is_safe' => ['html']]),
            new TwigFunction('humanize_tilename', [$this, 'humanizeTileName']),
        ];
    }

    public function renderTileSvg(string $tileSet, string $tileKey, int $size = 48): string
    {
        $tile = $this->provider->findByKey($tileSet, $tileKey);
        ob_start();
        echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"' . PHP_EOL;
        echo "width=\"$size\" height=\"$size\"" . PHP_EOL;
        echo "viewBox=\"-0.67 -0.67 1.34 1.34\">\n";
        echo $tile->getTile()->C14N();
        echo "</svg>\n";

        return ob_get_clean();
    }

    public function humanizeTileName(string $tilename): string
    {
        if (preg_match('#^cluster-([a-z]+)#', $tilename, $match)) {
            return ucfirst($match[1]);
        }

        return 'undefined';
    }

}
