<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Vertex;
use App\Form\PlaceType;
use App\Form\ProfileOnTheFly;
use App\Service\AvatarMaker;
use App\Service\PlayerCastCache;
use App\Service\WebsocketPusher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

/**
 * CRUD for Place
 */
class PlaceCrud extends GenericCrud
{

    /**
     * Creates a Place
     * @Route("/place/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(PlaceType::class, 'place/create.html.twig', $request);
    }

    /**
     * Edits a Place
     * @Route("/place/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(PlaceType::class, 'place/edit.html.twig', $pk, $request);
    }

    protected function createEntity(string $title): Vertex
    {
        return new Place($title);
    }

    /**
     * Show a list of NPC profiles for a Place
     * @Route("/place/npc/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function showNpc(string $pk): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        /** @var Place $vertex */
        $vertex = $this->repository->findByPk($pk);
        $card = 16;

        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $card; $k++) {
                $lang = random_int(0, 100) < 75 ? $vertex->surnameLang : 'random';
                $listing[$gender][] = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($lang);
            }
        }

        // form to post to generate profile on the fly
        $form = $this->createForm(ProfileOnTheFly::class, [
            'template' => $vertex->npcTemplate
        ]);

        return $this->render('place/npc_generate.html.twig', [
                'place' => $vertex,
                'listing' => $listing,
                'form' => $form->createView()
        ]);
    }

    /**
     * AJAX Create a social network profile for a NPC
     * @Route("/place/npc/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushProfile(string $pk, Request $request, AvatarMaker $maker, WebsocketPusher $client): JsonResponse
    {
        $form = $this->createForm(ProfileOnTheFly::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $param = $form->getData();
            $npc = $this->repository->findByTitle($param['template']);
            $npc->setTitle($param['name']);
            $profile = $maker->generate($npc, $this->convertSvgToPng($param['svg']));
            $path = \join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir, $param['name'] . '.png');
            imagepng($profile, $path);

            try {
                $client->push(json_encode([
                    'file' => $path,
                    'action' => 'pictureBroadcast'
                ]));

                return new JsonResponse(['level' => 'success', 'message' => 'Profile for ' . $param['name'] . ' pushed'], Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
            }
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
            throw new \RuntimeException($process->getErrorOutput());
        }

        return imagecreatefromstring($process->getOutput());
    }

    /**
     * Page for the battlemap
     * @Route("/place/battlemap/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function battlemap(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $url = $this->generateUrl('get_picture', ['title' => $vertex->battleMap]);

        return $this->render('map/running.html.twig', ['title' => $vertex->getTitle(), 'img' => $url]);
    }

}
