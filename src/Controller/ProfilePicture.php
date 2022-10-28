<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Transhuman;
use App\Form\ProfileOnTheFly;
use App\Form\ProfilePic;
use App\Repository\VertexRepository;
use App\Service\AvatarMaker;
use App\Service\BoringAvatar;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;
use function join_paths;

/**
 * Managing Social Networks Profile Pictures
 */
class ProfilePicture extends AbstractController
{

    protected $storage;
    protected $repository;

    public function __construct(Storage $store, VertexRepository $repo)
    {
        $this->storage = $store;
        $this->repository = $repo;
    }

    /**
     * Generate a socnet profile for a unique Transhuman
     * @Route("/profile/unique/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function unique(string $pk, AvatarMaker $maker): Response
    {
        $npc = $this->repository->findByPk($pk);
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, imagecreatefrompng($pathname->getPathname()));

        return new StreamedResponse(function () use ($profile) {
                    imagepng($profile);
                }, 200, ['content-type' => 'image/png']);
    }

    /**
     * Push a socnet profile for a unique Transhuman
     * @Route("/profile/unique/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushUnique(string $pk, AvatarMaker $maker, PlayerCastCache $cache): Response
    {
        $npc = $this->repository->findByPk($pk);
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, imagecreatefrompng($pathname->getPathname()));
        $path = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, $pk . '.png');
        imagepng($profile, $path);
        $cached = $cache->slimPictureForPush(new SplFileInfo($path));

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $cached->getPathname()]);
    }

    /**
     * Show a list of NPC profiles from a template (a Transhuman with isNpcTemplate() method returns true)
     * @Route("/profile/template/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function template(string $pk): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        /** @var Transhuman $vertex */
        $vertex = $this->repository->findByPk($pk);
        $card = 24;

        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $card; $k++) {
                $lang = random_int(0, 100) < 75 ? $vertex->surnameLang : 'random';
                $listing[$gender][] = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($lang);
            }
        }

        // the form to post for generating profile on the fly
        $form = $this->createForm(ProfileOnTheFly::class, [
            'template' => $vertex->getPk()
        ]);

        return $this->render('picture/random_npc.html.twig', [
                    'vertex' => $vertex,
                    'listing' => $listing,
                    'form' => $form->createView()
        ]);
    }

    /**
     * AJAX Create a social network profile for a NPC
     * @Route("/profile/template/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushTemplate(string $pk, Request $request, AvatarMaker $maker): JsonResponse
    {
        $form = $this->createForm(ProfileOnTheFly::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $param = $form->getData();
            $npc = $this->repository->findByPk($pk);
            $npc->setTitle($param['name']);
            $profile = $maker->generate($npc, imagecreatefrompng($param['avatar']->getPathname()));
            $path = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, $param['name'] . '.png');
            imagepng($profile, $path);

            return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $path]);
        }

        return new JsonResponse(['level' => 'error', 'message' => 'Invalid form'], Response::HTTP_FORBIDDEN);
    }

    /**
     * Creates a battlemap token for a NPC
     * @Route("/npc/token/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function token(string $pk, Request $request): Response
    {
        $npc = $this->repository->findByPk($pk);
        $form = $this->createForm(ProfilePic::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = 'token-' . $npc->getPk() . '.png';
            $this->storage->storeToken($form['avatar']->getData(), $filename);
            $npc->tokenPic = $filename;
            $this->repository->save($npc);
            $this->addFlash('success', 'Token généré');

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show a list of NPC profiles from a template (a Transhuman with isNpcTemplate() method returns true)
     * @Route("/profile/bauhaus/{pk}", methods={"GET", "POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function bauhaus(string $pk, Request $request, BoringAvatar $maker): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $npc = clone $vertex;
        $form = $this->createForm(ProfileOnTheFly::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // which button : push or create ?
        }

        $bauhaus = [];
        for ($k = 0; $k < 24; $k++) {
            $bauhaus[] = $maker->createBauhaus('yolo' . rand());
        }

        return $this->render('picture/profile_instantiate.html.twig', [
                    'form' => $form->createView(),
                    'bauhaus' => $bauhaus,
                    'npc' => $vertex
        ]);
    }

}
