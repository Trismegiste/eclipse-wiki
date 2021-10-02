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
    public function loveLetter(Request $request, \App\Repository\TraitProvider $pro): Response
    {
        $form = $this->createForm(LoveLetter::class);
        $listing = $pro->findAll('yolo');

        return $this->render('front/template_form.html.twig', ['pro' => $listing, 'form' => $form->createView()]);
    }

}
