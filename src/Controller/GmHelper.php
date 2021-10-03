<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\LoveLetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of GmHelper
 */
class GmHelper extends AbstractController
{

    /**
     * @Route("/gm/loveletter")
     */
    public function loveLetter(Request $request): Response
    {
        $form = $this->createForm(LoveLetter::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('loveletter.html.twig', $form->getData());
        }

        return $this->render('form.html.twig', ['title' => 'Love letter', 'form' => $form->createView()]);
    }

    /**
     * @Route("/gm/npc")
     */
    public function npcGenerator(Request $request): Response
    {
        $form = $this->createForm(\App\Form\Npc::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('loveletter.html.twig', $form->getData());
        }

        return $this->render('npc_form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/gm/background/info")
     */
    public function getBackground(Request $request, \App\Repository\BackgroundProvider $provider): Response
    {
        $key = $request->query->get('key');
        $bg = $provider->findOne($key);

        return $this->render('fragment/background_detail.html.twig', ['background' => $bg]);
    }

}
