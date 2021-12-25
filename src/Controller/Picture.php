<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Repository\VertexRepository;
use App\Service\AvatarMaker;
use App\Service\ObjectPushProcessFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\ImageTools\Entropy\Filter\SmartCrop;

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
    public function avatar(string $pk, Request $request, VertexRepository $repo): Response
    {
        $npc = $repo->findByPk($pk);

        $maker = new AvatarMaker();
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

    /**
     * Get a picture with a format/filter
     * @Route("/picture/{title}/{filter}", methods={"GET"})
     */
    public function send(string $title, string $filter): Response
    {
        $source = \join_paths($this->getUploadDir(), $title);

        if ($filter === 'original') {
            return new BinaryFileResponse($source);
        }

        list($width, $height, $image_type) = getimagesize($source);
        $img = imagecreatefromstring(file_get_contents($source));
        switch ($filter) {
            case 'entropy' :
                $edge = min($width, $height);
                $filter = new SmartCrop($edge, $edge);
                $resized = $filter->apply($img);

                return new StreamedResponse(function () use ($resized) {
                    imagejpeg($resized);
                }, Response::HTTP_OK, ['Content-type' => 'image/jpeg']);

            case 'center' :
                $crop = imagecrop($img, [
                    'x' => max(($width - $height) / 2, 0),
                    'y' => max(($height - $width) / 2, 0),
                    'width' => min($width, $height),
                    'height' => min($width, $height)
                ]);

                return new StreamedResponse(function () use ($crop) {
                    imagejpeg($crop);
                }, Response::HTTP_OK, ['Content-type' => 'image/jpeg']);
        }
    }

    /**
     * Create an avatar for NPC
     * @Route("/profile/create/{pk}", methods={"GET","POST"})
     */
    public function profile(string $pk, Request $request, VertexRepository $repo): Response
    {
        $npc = $repo->findByPk($pk);
        $maker = new AvatarMaker();

        $form = $this->createFormBuilder($npc)
            ->add('avatar', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'mapped' => false,
                'attr' => ['x-on:change' => 'readFile($el)']
            ])
            ->add('content', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class)
            ->add('generate', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            $socNetFolder = \join_paths($this->getParameter('kernel.project_dir'), 'public/socnet');
            $profilePic = $maker->generate($npc, $avatarFile->getPathname(), $socNetFolder);
            imagejpeg($profilePic, \join_paths($this->getUploadDir(), $npc->getTitle() . '-avatar.jpg'));

            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('app_vertexcrud_show', ['pk' => $pk]));
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

}
