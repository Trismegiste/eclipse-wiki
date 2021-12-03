<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of Idea
 *
 * @author flo
 */
class Idea extends AbstractController
{

    /**
     * @Route("/idea")
     */
    public function inspiration(Request $request, \App\Service\OpenAi $ai): Response
    {
        $form = $this->createFormBuilder()
                ->add('idea', TextareaType::class)
                ->add('work', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $source = $form->getData()['idea'];
            $work = $ai->request($source);

            $form = $this->createFormBuilder(['idea' => $source . $work])
                    ->add('idea', TextareaType::class, ['attr' => ['rows' => 32]])
                    ->add('work', SubmitType::class)
                    ->getForm();
        }

        return $this->render('form.html.twig', ['title' => 'OpenAi', 'form' => $form->createView()]);
    }

}
