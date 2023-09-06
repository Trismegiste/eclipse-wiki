<?php

/*
 * Eclipse Wiki
 */

namespace App\Voronoi;

use App\Entity\BattlemapDocument;
use App\Repository\TileProvider;
use App\Service\Storage;
use SplFileInfo;

/**
 * Dumps a battlemap to SVG
 */
class SvgDumper
{

    const defaultSizeForWeb = 1000;
    const resizeToken = 100;

    protected TileProvider $provider;
    protected Storage $storage;

    public function __construct(TileProvider $provider, Storage $storage)
    {
        $this->provider = $provider;
        $this->storage = $storage;
    }

    public function flush(BattlemapDocument $doc)
    {
        $side = $doc->side;
        $cos30 = sqrt(3) / 2.0;
        $width = $side / $cos30 + 1;  // because of the included rectangle in a hexagon
        $height = $side + 1;
        $pixelWidth = self::defaultSizeForWeb;
        $pixelHeight = (int) floor(self::defaultSizeForWeb * $cos30);

        echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"' . PHP_EOL;
        echo "width=\"$pixelWidth\" height=\"$pixelHeight\"" . PHP_EOL;
        echo "viewBox=\"-1 -1 $width $height\">\n";

        echo "<defs>\n";
        // declaring tiles
        foreach ($this->provider->getTileSet($doc->theme) as $svg) {
            echo $svg->getTile()->C14N();
        }

        // declaring tokens
        foreach ($doc->npcToken as $npcToken) {
            $tokenPic = $this->storage->getFileInfo($npcToken->picture);
            if ($tokenPic->isReadable()) {
                echo '<g id="' . $tokenPic->getBasename('.png') . '">';
                $this->dumpTokenFor($tokenPic);
                echo '</g>';
            }
        }
        echo "</defs>\n";

        // ground tiles
        echo '<g id="ground">' . PHP_EOL;
        foreach ($doc->grid as $cell) {
            echo "<use xlink:href=\"#{$cell->content->template}\" x=\"{$cell->x}\" y=\"{$cell->y}\"/>\n";
        }
        echo '</g>' . PHP_EOL;

        // walls
        echo '<g id="wall">' . PHP_EOL;
        foreach ($doc->grid as $cell) {
            $content = $cell->content;
            for ($direction = HexaCell::EAST; $direction < HexaCell::WEST; $direction++) {
                if ($content->wall[$direction]) {
                    $angle = -60 * $direction;
                    echo "<use xlink:href=\"#eastwall\" transform=\"translate({$cell->x} {$cell->y}) rotate($angle)\"/>\n";
                }
            }
        }
        echo '</g>' . PHP_EOL;

        // doors
        echo '<g id="door">' . PHP_EOL;
        foreach ($doc->grid as $cell) {
            $content = $cell->content;
            for ($direction = HexaCell::EAST; $direction <= HexaCell::WEST; $direction++) {
                if ($content->door[$direction]) {
                    $angle = -60 * $direction;
                    echo "<use xlink:href=\"#eastdoor\" transform=\"translate({$cell->x} {$cell->y}) rotate($angle)\"/>\n";
                }
            }
        }
        echo '</g>' . PHP_EOL;

        // npc
        echo '<g id="layer-npc">' . PHP_EOL;
        foreach ($doc->grid as $cell) {
            $content = $cell->content;
            if (!is_null($content->npc)) {
                printf('<use xlink:href="#%s" x="%f" y="%f" data-npc-title="%s"/>', basename($content->npc->picture, '.png'),
                        $cell->x, $cell->y, $content->npc->label);
            }
        }
        echo '</g>' . PHP_EOL;

        echo '</svg>';
    }

    public function dumpTokenFor(SplFileInfo $tokenPic): void
    {
        $source = imagecreatefrompng($tokenPic->getPathname());
        $target = imagescale($source, self::resizeToken, self::resizeToken, IMG_BICUBIC_FIXED);

        echo '<g transform="scale(0.008) translate(-50, -50)">';
        echo '<image width="100" height="100" xlink:href="data:image/png;base64,';
        imagesavealpha($target, true);
        ob_start();
        imagepng($target);
        echo base64_encode(ob_get_clean());
        echo '"/>';
        echo '<circle cx="50" cy="50" r="50" style="fill:none;stroke:red;stroke-width:5;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />';
        echo "</g>\n";
    }

}
