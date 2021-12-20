<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for pictures
 */
class Picture extends AbstractController
{

    /**
     * Ajax for searching local images
     * @Route("/picture/search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');

        $finder = new Finder();
        $it = $finder->in(join_paths($this->getParameter('kernel.project_dir'), 'public/upload'))
                ->files()
                ->name("/$title/i");

        $choice = [];
        foreach ($it as $fch) {
            $choice[] = $fch->getBasename();
        }

        return new JsonResponse($choice);
    }

    /**
     * Show image
     * @Route("/picture/show/{title}", methods={"GET"})
     */
    public function show(string $title): Response
    {
        return $this->render('picture/show.html.twig', ['img' => $title]);  // @todo security issue
    }

    /**
     * Send an image to external device
     * @Route("/picture/send/{title}", methods={"GET"})
     */
    public function bluetooth(string $title): JsonResponse
    {
        $bluetooth = $this->getParameter('auxiliary_screen');
        $process = new Process(['obexftp',
            '--nopath',
            '--noconn',
            '--uuid', 'none',
            '--bluetooth', $bluetooth['mac'],
            '--channel', $bluetooth['channel'],
            '--put', join_paths($this->getParameter('kernel.project_dir'), 'public/upload', $title)
        ]);
        $process->run();

        return new JsonResponse(null, 200);
    }

}
