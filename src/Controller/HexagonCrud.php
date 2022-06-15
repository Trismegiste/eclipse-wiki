<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\TileArrangementType;
use App\Repository\TileArrangementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class HexagonCrud extends AbstractController
{

    protected $tileRepo;

    public function __construct(TileArrangementRepository $repo)
    {
        $this->tileRepo = $repo;
    }

    /**
     * @Route("/tile/arrangement/create")
     */
    public function createSet(Request $request): Response
    {
        $form = $this->createForm(TileArrangementType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $obj = $form->getData();
            $this->tileRepo->save($obj);
            $this->addFlash('success', 'Collection sauvegardée');

            return $this->redirectToRoute('app_hexagoncrud_editset', ['pk' => $obj->getPk()]);
        }

        return $this->render('hex/set_create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tile/arrangement/edit/{pk}")
     */
    public function editSet(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
            ->add('collection', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => \App\Form\HexagonalTileType::class,
            ])
            ->add('edit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
            $this->addFlash('success', 'Adjacences sauvegardées');
        }

        return $this->render('hex/set_edit.html.twig', ['form' => $form->createView()]);
    }

}
