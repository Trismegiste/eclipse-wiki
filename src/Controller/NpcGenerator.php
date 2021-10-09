<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Form\NpcCreate;
use App\Form\NpcStats;
use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Generator for NPC
 */
class NpcGenerator extends AbstractController
{

    protected $repository;

    public function __construct(Repository $characterRepo)
    {
        $this->repository = $characterRepo;
    }

    /**
     * @Route("/npc/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(NpcCreate::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc_create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/npc/edit/{pk}", methods={"GET","POST"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);
        $form = $this->createForm(NpcStats::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_create');
        }

        return $this->render('npc_edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/npc/background/info")
     */
    public function getBackground(Request $request, BackgroundProvider $provider): Response
    {
        $key = $request->query->get('key');
        $bg = $provider->findOne($key);

        return $this->render('fragment/background_detail.html.twig', ['background' => $bg]);
    }

    /**
     * @Route("/npc/faction/info", name="app_npcgenerator_getfaction")
     */
    public function getFaction(Request $request, FactionProvider $provider): Response
    {
        $key = $request->query->get('key');
        $fac = $provider->findOne($key);

        return $this->render('fragment/faction_detail.html.twig', ['faction' => $fac]);
    }

    /**
     * @Route("/npc/morph/info")
     */
    public function getMorph(Request $request, MorphProvider $provider): Response
    {
        $key = $request->query->get('key');
        $obj = $provider->findOne($key);

        return $this->render('fragment/morph_detail.html.twig', ['morph' => $obj]);
    }

}
