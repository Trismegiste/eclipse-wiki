<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Entity\MapConfig;
use RuntimeException;

/**
 * Service for creating a HexaMap
 */
class MapBuilder
{

    const VOID_UID = 0;
    const HALLWAY_UID = 10;
    const ROOM_UID = 100;
    const defaultSizeForWeb = 1000;

    protected $tilePath;

    public function __construct(string $tilePath)
    {
        $this->tilePath = $tilePath;
    }

    public function create(MapConfig $config): HexaMap
    {
        $map = new HexaMap($config->side);

        srand($config->seed);
        $draw = new MapDrawer($map);

        $draw->plantRandomSeed(new HexaCell(self::ROOM_UID, 'room'), $config->avgTilePerRoom);

        $hallway = new HexaCell(self::HALLWAY_UID, 'default', false);

        if ($config->horizontalLines > 0) {
            $draw->horizontalCross($hallway, $config->horizontalLines, $config->doubleHorizontal);
        }
        if ($config->verticalLines > 0) {
            $draw->verticalCross($hallway, $config->verticalLines, $config->doubleVertical);
        }

        $config->container->draw($draw);

        $current = $map->iterateNeighbourhood();
        // we iterates as long as the count of empty cells is shrinking on each iteration
        do {
            $lastEmpty = $current;
            $current = $map->iterateNeighbourhood();
        } while ($current < $lastEmpty);

        // if there are still empty cells, stops generation and throw exception
        if ($current > 0) {
            throw new RuntimeException("Cannot fill $current cells with Voronoi iterations");
        }

        if ($config->erosion) {
            $map->erodeWith($hallway, $config->erodingMinRoomSize, $config->erodingMaxNeighbour);
        }

        $map->wallProcessing();

        return $map;
    }

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
        foreach (['default', 'eastwall', 'eastdoor', 'room', 'void', 'fogofwar'] as $filename) {
            $svg = new TileSvg();
            $svg->load("{$this->tilePath}/$filename.svg");
            echo $svg->getTile()->C14N();
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

}
