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
use App\Service\PlayerCastCache;
use App\Service\Storage;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
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
     * Create an avatar for NPC
     * @Route("/profile/create/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function create(string $pk, Request $request, VertexRepository $repo, AvatarMaker $maker): Response
    {
        $npc = $repo->findByPk($pk);
        $form = $this->createForm(ProfilePic::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();
            $profilePic = $maker->generate($npc, imagecreatefromstring($avatarFile->getContent()));
            $filename = $npc->getTitle() . '-avatar.png';
            imagepng($profilePic, join_paths($this->storage->getRootDir(), $filename));
            if (!$npc->hasAvatarSection()) {
                $npc->appendAvatarSection($filename);
                $repo->save($npc);
            }
            $this->addFlash('success', 'Profil réseaux sociaux généré');

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show a list of NPC profiles for a Place
     * @Route("/profile/onthefly/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function generateOnTheFly(string $pk): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        /** @var Transhuman $vertex */
        $vertex = $this->repository->findByPk($pk);
        $card = 24;

        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $card; $k++) {
                $lang = 'random';//random_int(0, 100) < 75 ? $vertex->surnameLang : 'random';
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
     * @Route("/profile/onthefly/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushProfile(string $pk, Request $request, AvatarMaker $maker): JsonResponse
    {
        $form = $this->createForm(ProfileOnTheFly::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $param = $form->getData();
            $npc = $this->repository->findByPk($param['template']);
            $npc->setTitle($param['name']);
            $profile = $maker->generate($npc, $this->convertSvgToPng($param['svg']));
            $path = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, $param['name'] . '.png');
            imagepng($profile, $path);

            return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $path]);
        }

        return new JsonResponse(['level' => 'error', 'message' => 'Invalid form'], Response::HTTP_FORBIDDEN);
    }

    private function convertSvgToPng(string $svg)
    {
        $input = new InputStream();
        $process = new Process([
            'convert',
            '-background', 'none',
            '-strokewidth', 0,
            '-density', 200,
            '-resize', '503x503',
            'svg:-', 'png:-'
        ]);
        $process->setInput($input);
        $process->start();
        $input->write($svg);
        $input->close();
        $process->wait();

        if (0 !== $process->getExitCode()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return imagecreatefromstring($process->getOutput());
    }

}
