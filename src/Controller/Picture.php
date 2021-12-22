<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\ObjectPushProcessFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $it = $finder->in($this->getUploadDir())
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
    public function bluetooth(string $title, ObjectPushProcessFactory $fac): JsonResponse
    {
        $process = $fac->create(\join_paths($this->getUploadDir(), $title));
        $process->run();

        return new JsonResponse(null, 200);
    }

    protected function getUploadDir(): string
    {
        return \join_paths($this->getParameter('kernel.project_dir'), 'public/upload');
    }

    /**
     * Create an avatar for NPC
     * @Route("/profile/generate/{pk}", methods={"GET","POST"})
     */
    public function avatar(string $pk, Request $request, \App\Repository\VertexRepository $repo): Response
    {
        $npc = $repo->findByPk($pk);

        $maker = new \App\Service\AvatarMaker();
        $image = $maker->getImageChoice($npc);
        if (count($image) !== 0) {
            $choice = array_key_first($image);
            $avatar = $maker->generate($npc, \join_paths($this->getUploadDir(), $choice), \join_paths($this->getParameter('kernel.project_dir'), 'public/socnet'));
            $filename = $npc->getTitle() . '-avatar.jpg';
            imagejpeg($avatar, \join_paths($this->getUploadDir(), $filename));

            $npc->setContent($npc->getContent() . "\n==Avatar==\n[[file:$filename]]\n");
            $repo->save($npc);
        }

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $pk]);
    }

}
