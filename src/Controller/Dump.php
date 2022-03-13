<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\EdgeProvider;
use App\Repository\HindranceProvider;
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

        return $this->render('dump/edge.html.twig', ['listing' => $listing]);
    }

    /**
     * Dumps all skills
     * @Route("/dump/skill", methods={"GET"})
     */
    public function skill(SkillProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump/skill.html.twig', ['listing' => $listing]);
    }

    /**
     * Dumps all hindrances
     * @Route("/dump/hindrance", methods={"GET"})
     */
    public function hindrance(HindranceProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump/hindrance.html.twig', ['listing' => $listing]);
    }

    /**
     * Dumps all gears
     * @Route("/dump/gear", methods={"GET"})
     */
    public function gear(\App\Repository\GearProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump/gear.html.twig', ['listing' => $listing]);
    }

    /**
     * Dumps all ranged weapons
     * @Route("/dump/rw", methods={"GET"})
     */
    public function rangedWeapon(\App\Repository\RangedWeaponProvider $repo)
    {
        $listing = $repo->getListing();

        return $this->render('dump/rangedw.html.twig', ['listing' => $listing]);
    }

}
