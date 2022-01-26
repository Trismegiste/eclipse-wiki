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
use App\Service\ObjectPushFactory;
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
        $vertex = $this->repository->findByPk($pk);
        $card = 16;

        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $card; $k++) {
                $lang = random_int(0, 100) < 75 ? $vertex->surnameLang : 'random';
                $listing[$gender][] = $repo->getRandomGivenNameFor($gender, 'random') . ' ' . $repo->getRandomSurnameFor($lang);
            }
        }

        return $this->render('place/npc_generate.html.twig', ['place' => $vertex, 'listing' => $listing]);
    }

    /**
     * AJAX Create a social network profile for a NPC
     * @Route("/place/profile/create", methods={"POST"})
     */
    public function generateProfile(Request $request, AvatarMaker $maker): Response
    {
        $form = $this->createForm(ProfileOnTheFly::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $param = $form->getData();
            $npc = $this->repository->findByTitle($param['template']);
            $npc->setTitle($param['name']);
            $profile = $maker->generate($npc, $this->convertSvgToPng($param['svg']));

            $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($profile) {
                        imagepng($profile);
                    },
                    Response::HTTP_CREATED,
                    ['Content-Type' => 'image/png']
            );

            return $response;
        }

        throw new \Symfony\Component\Form\Exception\RuntimeException('yolo');
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
     * Popup for on the fly NPC avatar
     * @Route("/place/profile/create", methods={"GET"})
     */
    public function npcPopup(Request $request): Response
    {
        $name = $request->query->get('name');
        $npc = $this->repository->findByTitle($request->query->get('template'));
        $form = $this->createForm(ProfileOnTheFly::class);

        return $this->render('place/npc_popup.html.twig', [
                    'name' => $name,
                    'npc' => $npc,
                    'form' => $form->createView()
        ]);
    }

}
