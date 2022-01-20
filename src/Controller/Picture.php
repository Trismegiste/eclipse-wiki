<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\ProfilePic;
use App\Repository\VertexRepository;
use App\Service\AvatarMaker;
use App\Service\ObjectPushFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function bluetooth(string $title, ObjectPushFactory $fac): JsonResponse
    {
        $fac->send(\join_paths($this->getUploadDir(), $title));

        return new JsonResponse(null, 200);
    }

    protected function getUploadDir(): string
    {
        return \join_paths($this->getParameter('kernel.project_dir'), 'public/upload');
    }

    /**
     * Create an avatar for NPC
     * @Route("/profile/create/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function profile(string $pk, Request $request, VertexRepository $repo, AvatarMaker $maker): Response
    {
        $npc = $repo->findByPk($pk);
        $form = $this->createForm(ProfilePic::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            $profilePic = $maker->generate($npc, $avatarFile->getPathname());
            $filename = $npc->getTitle() . '-avatar.jpg';
            imagejpeg($profilePic, \join_paths($this->getUploadDir(), $filename));
            $append = "\n==Avatar==\n[[file:$filename]]\n";
            $npc->setContent($npc->getContent() . $append);
            $repo->save($npc);
            $this->addFlash('success', 'Profil réseaux sociaux généré');

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

}
