<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\EdgeProvider;
use App\Repository\SkillProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Dump table for importing in other software
 */
class Dump extends AbstractController
{

    /**
     * Dumps all edges
     * @Route("/dump/edge", methods={"GET"})
     */
    public function edge(EdgeProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump-edge.html.twig', ['listing' => $listing]);
    }

    /**
     * Dumps all skills
     * @Route("/dump/skill", methods={"GET"})
     */
    public function skill(SkillProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump-skill.html.twig', ['listing' => $listing]);
    }

}
