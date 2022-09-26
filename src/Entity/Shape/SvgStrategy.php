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

        $html = <<<YOLO
<html>
<head>
    <style>
        body {
            width: {$edge}px;
            margin: 0;
            padding: 0;
        }
        svg {
            width: 100%;
            margin: 0;
        }
    </style>
</head>
<body>
    {$this->svgContent}
</body>
</html>
YOLO;

        // extensions are important for wkhtmltopng
        $htmlTarget = sys_get_temp_dir() . '/' . $basename . '.html';
        $pngTarget = sys_get_temp_dir() . '/' . $basename . '.png';
        file_put_contents($htmlTarget, $html);
        $matrixing = new Process([
            'wkhtmltoimage',
            '--width', $edge,
            '--height', $edge,
            $htmlTarget,
            $pngTarget
        ]);
        $matrixing->mustRun();

        $raster = imagecreatefrompng($pngTarget);

        $draw->fillWithPicture($filling, $raster);
    }

    public function getName(): string
    {
        return $this->code;
    }

}
