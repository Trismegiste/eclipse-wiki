<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Entity\MapConfig;
use App\Repository\TileProvider;
use App\Service\Storage;
use RuntimeException;

/**
 * Service for creating a HexaMap
 */
class MapBuilder
{

    const defaultSizeForWeb = 1000;
    const resizeToken = 100;

    protected TileProvider $provider;
    protected Storage $storage;
    protected MapDrawer $drawer;

    public function __construct(TileProvider $provider, Storage $storage, MapDrawer $draw)
    {
        $this->provider = $provider;
        $this->storage = $storage;
        $this->drawer = $draw;
    }

    /**
     * Builds the battlemap with all parameters from MapConfig
     * @param MapConfig $config
     * @return HexaMap
     * @throws RuntimeException
     */
    public function create(MapConfig $config): HexaMap
    {
        $map = new HexaMap($config->side);

        srand($config->seed);
        $this->drawer->setMap($map);

        $this->drawer->plantRandomSeed(new HexaCell(HexaCell::CLUSTER_UID, 'cluster'), $config->avgTilePerRoom);

        $hallway = new HexaCell(HexaCell::SPACING_UID, 'default', false);

        if ($config->horizontalLines > 0) {
            $this->drawer->horizontalCross($hallway, $config->horizontalLines, $config->doubleHorizontal);
        }
        if ($config->verticalLines > 0) {
            $this->drawer->verticalCross($hallway, $config->verticalLines, $config->doubleVertical);
        }

        $config->container->draw($this->drawer);

        $current = $map->iterateNeighbourhood();
        // we iterates as long as the count of empty cells is shrinking on each iteration
        do {
            $lastEmpty = $current;
            $current = $map->iterateNeighbourhood();
        } while ($current < $lastEmpty);

        // if there are still empty cells, stops generation and throw exception
        if ($current > 0) {
            throw new RuntimeException("Cannot fill $current remaining cells with Voronoi iterations");
        }

        if ($config->erosion) {
            $map->erodeWith($hallway, $config->erodingMinRoomSize, $config->erodingMaxNeighbour);
        }

        $map->wallProcessing();
        $map->texturing($config->tileWeight, $config->minClusterPerTile);
        $map->populating($config->tilePopulation);

        return $map;
    }

    /**
     * Dumps the SVG of a battlemap
     * @param HexaMap $map
     * @param bool $withFogOfWar With fog or not
     */
    public function dumpSvg(HexaMap $map, bool $withFogOfWar = true): void
    {
        $side = $map->getSize();
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
        foreach ($this->provider->getTileSet('habitat') as $svg) {  // @todo remove hardcoded, this value is coming from MapConfig
            echo $svg->getTile()->C14N();
        }

        foreach ($map->getNpcToken() as $npcToken) {
            $tokenPic = $this->storage->getFileInfo($npcToken->picture);
            if ($tokenPic->isReadable()) {
                echo '<g id="' . $tokenPic->getBasename('.png') . '">';
                $this->dumpTokenFor($tokenPic);
                echo '</g>';
            }
        }

        echo "</defs>\n";

        echo '<g id="ground">' . PHP_EOL;
        $map->dumpGround();
        echo '</g>' . PHP_EOL;

        echo '<g id="wall">' . PHP_EOL;
        $map->dumpWall();
        echo '</g>' . PHP_EOL;

        echo '<g id="door">' . PHP_EOL;
        $map->dumpDoor();
        echo '</g>' . PHP_EOL;

        echo '<g id="legend">' . PHP_EOL;
        $map->dumpLegend();
        echo '</g>' . PHP_EOL;

        echo '<g id="layer-npc">' . PHP_EOL;
        $map->dumpNpc();
        echo '</g>' . PHP_EOL;

        echo '<g id="gm-fogofwar">' . PHP_EOL;
        if ($withFogOfWar) {
            $map->dumpFogOfWar();
        }
        echo '</g>' . PHP_EOL;

        echo '</svg>';
    }

    /**
     * Writes a map SVG content to a file
     * @param HexaMap $map
     * @param string $pathname
     */
    public function save(HexaMap $map, string $pathname): void
    {
        $target = fopen($pathname, 'w');
        ob_start(function (string $buffer) use ($target) {
            fwrite($target, $buffer);
        }, 10000);
        $this->dumpSvg($map);
        ob_end_flush();
        fclose($target);
    }

    public function dumpTokenFor(\SplFileInfo $tokenPic): void
    {
        $source = imagecreatefrompng($tokenPic->getPathname());
        $target = imagescale($source, self::resizeToken, self::resizeToken, IMG_BICUBIC_FIXED);

        echo '<g transform="scale(0.008)">';   // translate(-50, -50) is missing because of bug in svg.draggable
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
