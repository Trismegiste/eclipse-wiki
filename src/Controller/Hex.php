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
                    echo '<svg viewBox="0 0 20 20" width="600" height="600">';

                    $cos60 = cos(M_PI / 3);
                    $sin60 = sin(M_PI / 3);
                    $minusCos60 = -$cos60;
                    $minusSin60 = -$sin60;
                    echo <<<YOLO
                        <defs>
                            <g id="hexmap" style="stroke: black; stroke-width: 0.02"" 
                                fill="#ddd" 
                                transform="rotate(30) scale(0.66666)">
                                <path d="M 1 0
                                    L $cos60 $sin60 
                                    L $minusCos60 $sin60 
                                    L -1 0
                                    L $minusCos60 $minusSin60
                                    L $cos60 $minusSin60
                                    L 1 0"/>
                            </g>

                            <g id="redmap" style="stroke: black; stroke-width: 0.02"" 
                                fill="red" 
                                transform="rotate(30) scale(0.66666)">
                                <path d="M 1 0
                                    L $cos60 $sin60 
                                    L $minusCos60 $sin60 
                                    L -1 0
                                    L $minusCos60 $minusSin60
                                    L $cos60 $minusSin60
                                    L 1 0"/>
                            </g>

                        </defs>
YOLO;

                    $map = new \App\Entity\HexagonTopography(20);
                    for ($x = 0; $x < 20; $x++) {
                        for ($y = 0; $y < 20; $y++) {
                            $map->set($x, $y, (($x === 10) || ($y === 10)) ? 'redmap' : 'hexmap');
                        }
                    }
                    $map->printSvg();

                    echo '</svg>';
                });
    }

}
