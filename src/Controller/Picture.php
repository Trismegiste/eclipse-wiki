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

    protected $storage;

    public function __construct(\App\Service\Storage $store)
    {
        $this->storage = $store;
    }

    /**
     * Ajax for searching local images
     * @Route("/picture/search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        $it = $this->storage->searchByTitle($title);

        $choice = [];
        foreach ($it as $fch) {
            $choice[] = $fch->getBasename();
        }

        return new JsonResponse($choice);
    }

    /**
     * Show image in a popup
     * @Route("/picture/popup/{title}", methods={"GET"})
     */
    public function popup(string $title): Response
    {
        $path = \join_paths($this->storage->getRootDir(), $title);
        if (!file_exists($path)) {
            throw $this->createNotFoundException($title);
        }
        list($width, $height) = getimagesize($path);

        $sidePlus = max([$width, $height]);
        $coord = $this->getParameter('second_screen');
        if ($sidePlus > $coord['max_size']) {
            $height = round($coord['max_size'] * $height / $sidePlus);
            $width = round($coord['max_size'] * $width / $sidePlus);
        }

        return $this->render('picture/popup.html.twig', [
                    'img' => $this->generateUrl('get_picture', ['title' => $title]),
                    'sx' => $width + $coord['delta_x'],
                    'sy' => $height + $coord['delta_y']
        ]);
    }

    /**
     * Send an image to external device
     * @Route("/picture/send/{title}", methods={"GET"})
     */
    public function bluetooth(string $title, ObjectPushFactory $fac): JsonResponse
    {
        $fac->send(\join_paths($this->storage->getRootDir(), $title));

        return new JsonResponse(null, Response::HTTP_OK);
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
            $profilePic = $maker->generate($npc, imagecreatefromstring($avatarFile->getContent()));
            $filename = $npc->getTitle() . '-avatar.jpg';
            imagejpeg($profilePic, \join_paths($this->storage->getRootDir(), $filename));
            $append = "\n==Avatar==\n[[file:$filename]]\n";
            $npc->setContent($npc->getContent() . $append);
            $repo->save($npc);
            $this->addFlash('success', 'Profil réseaux sociaux généré');

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show image from storage
     * @Route("/picture/get/{title}", name="get_picture", methods={"GET"})
     */
    public function read(string $title): Response
    {
        return $this->storage->createResponse($title);
    }

}
