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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @Route("/npc/list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->render('npc/list.html.twig', ['listing' => $this->repository->search()]);
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

        return $this->render('npc/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/npc/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);
        $form = $this->createForm(NpcStats::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_list');
        }

        return $this->render('npc/edit.html.twig', ['form' => $form->createView()]);
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

    /**
     * @Route("/npc/show/{pk}", methods={"GET"})
     */
    public function show(string $pk): Response
    {
        $npc = $this->repository->load($pk);

        return $this->render('npc/show.html.twig', ['npc' => $npc]);
    }

    /**
     * @Route("/npc/delete/{pk}", methods={"GET","DELETE"})
     */
    public function delete(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);
        $form = $this->createFormBuilder($npc)
                ->add('delete', SubmitType::class)
                ->setMethod('DELETE')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->delete($npc);

            return $this->redirectToRoute('app_npcgenerator_list');
        }

        return $this->render('npc/delete.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/npc/gear/{pk}", methods={"GET","PUT"})
     */
    public function gear(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);

        return $this->render('npc/gear.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/npc/duplicate/{pk}", methods={"GET","POST"})
     */
    public function duplicate(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);
        $newNpc = clone $npc;

        $form = $this->createFormBuilder($newNpc)
                ->add('name', TextType::class)
                ->add('wildCard', CheckboxType::class, ['required' => false])
                ->add('copy', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($newNpc);

            return $this->redirectToRoute('app_npcgenerator_list');
        }

        return $this->render('form.html.twig', ['title' => 'Duplicate ' . $npc->name, 'form' => $form->createView()]);
    }

    /**
     * @Route("/npc/battle/{pk}", methods={"GET","PUT"})
     */
    public function battle(string $pk, Request $request): Response
    {
        $npc = $this->repository->load($pk);

        return $this->render('npc/battle.html.twig', ['form' => $form->createView()]);
    }

}
