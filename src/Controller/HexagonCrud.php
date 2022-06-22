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

            return $this->redirectToRoute('app_hexagoncrud_editanchor', ['pk' => $obj->getPk()]);
        }

        return $this->render('hex/set_create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tile/arrangement/anchor/{pk}")
     */
    public function editAnchor(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
                ->add('collection', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                    'entry_type' => \App\Form\TileAnchorType::class,
                ])
                ->add('edit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
            $this->addFlash('success', 'Adjacences sauvegardées');

            return $this->redirectToRoute('app_hexagoncrud_editrotation', ['pk' => $arrang->getPk()]);
        }

        return $this->render('hex/set_anchor.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tile/arrangement/rotation/{pk}")
     */
    public function editRotation(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
                ->add('collection', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                    'entry_type' => \App\Form\TileRotationType::class,
                ])
                ->add('edit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
            $this->addFlash('success', 'Rotations sauvegardées');

            return $this->redirectToRoute('app_hexagoncrud_generate', ['pk' => $arrang->getPk()]);
        }

        return $this->render('hex/set_rotation.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tile/arrangement/generate/{pk}")
     */
    public function generate(string $pk): Response
    {
        /** @var \App\Entity\TileArrangement $arrang */
        $arrang = $this->tileRepo->load($pk);

        // compil anchors
        $tileDic = [];
        $anchor = [];
        foreach ($arrang->getCollection() as $tile) {
            foreach ($tile->getRotation() as $idx => $isPresent) {
                if ($isPresent) {
                    $eigen = new \App\Entity\Wfc\EigenTile();
                    $eigen->filename = $tile->filename;
                    $eigen->rotation = 60 * $idx;

                    $tileDic[] = $eigen;
                    $tmp = $tile->getAnchor();
                    for ($k = 0; $k < $idx; $k++) {
                        $lastItem = array_pop($tmp);
                        array_unshift($tmp, $lastItem);
                    }
                    $anchor[] = $tmp;
                }
            }
        }

        // compute neighbors masks
        foreach ($tileDic as $centerIdx => $centerTile) {
            for ($direction = 0; $direction < 6; $direction++) {
                $mask = 0;
                foreach ($tileDic as $neighborIdx => $neighborTile) {
                    if ($anchor[$centerIdx][$direction] === $anchor[$neighborIdx][($direction + 3) % 6]) {
                        $mask = $mask | (1 << $neighborIdx);
                    }
                }
                $centerTile->neighborMask[$direction] = $mask;
            }
        }

        // fill wave function
        $edgeSize = 20;
        $wf = new \App\Entity\Wfc\WaveFunction($edgeSize);
        for ($x = 0; $x < $edgeSize; $x++) {
            for ($y = 0; $y < $edgeSize; $y++) {
                $cell = new \App\Entity\Wfc\WaveCell();
                $cell->tileMask = (1 << count($tileDic)) - 1;
                $wf->setTile([$x, $y], $cell);
            }
        }

        // launch
        $wf->collapse([$edgeSize / 2, $edgeSize / 2], 0b00001);

        return $this->render('hex/generate.html.twig', ['tile' => $tileDic, 'anchor' => $anchor, 'map' => $wf]);
    }

}
