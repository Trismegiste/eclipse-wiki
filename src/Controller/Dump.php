<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\EdgeProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of Dump
 *
 * @author flo
 */
class Dump extends AbstractController
{

    /**
     * @Route("/dump/edge")
     */
    public function edge(EdgeProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump-edge.html.twig', ['listing' => $listing]);
    }

}
