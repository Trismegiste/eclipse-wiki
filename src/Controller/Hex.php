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
                    echo '<svg viewBox="-10 -10 20 20" width="600" height="600">';

                    $cos60 = cos(M_PI / 3);
                    $sin60 = sin(M_PI / 3);
                    $minusCos60 = -$cos60;
                    $minusSin60 = -$sin60;
                    echo <<<YOLO
                        <defs>
                            <g id="hexmap" style="stroke: black; stroke-width: 0.05" stroke-opacity="0.3">
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

                    for ($x = -10; $x < 10; $x+=2) {
                        for ($y = -10; $y < 10; $y+=2) {
                            $cx = $x + $y * cos(M_PI / 3);
                            $cy = -$y * sin(M_PI / 3);
                            echo "<use x=\"$cx\" y=\"$cy\" href=\"#hexmap\"/>";
                        }
                    }
                    echo '</svg>';
                });
    }

}
