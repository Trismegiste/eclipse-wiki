<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of Hex
 *
 * @author trismegiste
 */
class Hex extends AbstractController
{

    /**
     * @Route("/hex/show")
     */
    public function show(): Response
    {
        return $this->render('hex/show.html.twig');
    }

    /**
     * @Route("/hex/map")
     */
    public function map(): Response
    {
        return new StreamedResponse(function () {
                    echo '<svg viewBox="0 0 40 40" width="800" height="800">';

                    $cos60 = cos(M_PI / 3);
                    $sin60 = sin(M_PI / 3);
                    $minusCos60 = -$cos60;
                    $minusSin60 = -$sin60;
                    echo <<<YOLO
                        <defs>
                            <g id="hexmap" transform="rotate(30) scale(0.666666)">
                                <path style="stroke: black; stroke-width: 0.02"
                                fill="#ddd" d="M 1 0
                                    L $cos60 $sin60 
                                    L $minusCos60 $sin60 
                                    L -1 0
                                    L $minusCos60 $minusSin60
                                    L $cos60 $minusSin60
                                    L 1 0"/>
                            </g>

                            <g id="redmap" transform="rotate(30) scale(0.666666)">
                                <path style="stroke: black; stroke-width: 0.02"
                                fill="red" d="M 1 0
                                    L $cos60 $sin60 
                                    L $minusCos60 $sin60 
                                    L -1 0
                                    L $minusCos60 $minusSin60
                                    L $cos60 $minusSin60
                                    L 1 0"/>
                            </g>

                        </defs>
YOLO;

                    $map = new \App\Entity\HexagonTopography(40);
                    for ($x = 0; $x < 40; $x++) {
                        for ($y = 0; $y < 40; $y++) {
                            $map->setTile([$x, $y], 'hexmap');
                        }
                    }
                    foreach ($map->getNeighbourCoordinates([13, 13]) as $coord) {
                        $map->setTile($coord, 'redmap');
                    }
                    foreach ($map->getNeighbourCoordinates([26, 26]) as $coord) {
                        $map->setTile($coord, 'redmap');
                    }

                    $map->printSvg();

                    echo '</svg>';
                });
    }

}
