<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Repository\MorphProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Generator for NPC
 */
class NpcGenerator extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * @Route("/npc/morph/info")
     */
    public function getMorph(Request $request, MorphProvider $provider): Response
    {
        $key = $request->query->get('key');
        $obj = $provider->findOne($key);

        return $this->render('fragment/morph_detail.html.twig', ['morph' => $obj]);
    }

}
