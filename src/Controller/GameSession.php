<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Service\GameSessionTracker;
use App\Service\SessionPushHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Tracking actions for the current Game Session
 */
#[Route('/session')]
class GameSession extends AbstractController
{

    public function __construct(protected SessionPushHistory $broadcastHistory)
    {
        
    }

    /**
     * Shows the history of the GM
     * @param GameSessionTracker $tracker
     * @return Response
     */
    public function history(GameSessionTracker $tracker): Response
    {
        return $this->render('gamesession/history.html.twig', ['document' => $tracker->getDocument()]);
    }

    #[Route('/broadcast-export', methods: ['GET', 'POST'])]
    public function broadcastExport(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $form = $this->createFormBuilder()
                ->add('picture', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $this->broadcastHistory->getListing(),
                    'choice_value' => 'filename'
                ])
                ->add('export', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
        }

        return $this->render('gamesession/broadcasted.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * Show broadcasted picture from cache
     */
    #[Route('/broadcasted-picture/{title}', methods: ['GET'])]
    public function broadcasted(string $title): Response
    {
        return $this->broadcastHistory->createResponse($title);
    }

}
