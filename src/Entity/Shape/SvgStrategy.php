<?php

namespace App\Entity\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapDrawer;
use Symfony\Component\Process\Process;

class SvgStrategy extends Strategy
{

    protected string $code;
    protected string $svgContent;

    public function __construct(string $name, string $svg)
    {
        $this->code = 'STRAT_SVG_' . strtoupper($name);
        $this->svgContent = $svg;
    }

    public function draw(MapDrawer $draw): void
    {
        $filling = new HexaCell(HexaCell::VOID_UID, 'void', false);
        $edge = $draw->getCanvasSize();
        $basename = "target" . rand();

        $svgTarget = sys_get_temp_dir() . '/' . $basename . '.svg';
        $pngTarget = sys_get_temp_dir() . '/' . $basename . '.png';
        file_put_contents($svgTarget, $this->svgContent);
        $matrixing = new Process([
            'convert',
            '-size', $edge . 'x' . $edge,
            $svgTarget,
            '-depth', 8,
            '-set', 'colorspace', 'Gray',
            $pngTarget
        ]);
        $matrixing->mustRun();

        $raster = imagecreatefrompng($pngTarget);
        unlink($svgTarget);

        $draw->fillWithPicture($filling, $raster);
        imagedestroy($raster);
        unlink($pngTarget);
    }

    public function getName(): string
    {
        return $this->code;
    }

}
