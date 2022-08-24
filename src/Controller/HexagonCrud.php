<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\TileArrangement;
use App\Entity\Wfc\BattlemapSvg;
use App\Entity\Wfc\Factory;
use App\Entity\Wfc\TileSvg;
use App\Form\Tile\AnchorType;
use App\Form\Tile\ArrangementType;
use App\Form\Tile\RotationType;
use App\Form\Tile\WeightType;
use App\Repository\TileArrangementRepository;
use App\Voronoi\HexaCell;
use App\Voronoi\HexaMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @Route("/tileset/create")
     */
    public function createSet(Request $request): Response
    {
        $form = $this->createForm(ArrangementType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $obj = $form->getData();
            $this->tileRepo->save($obj);
            $this->addFlash('success', 'Collection sauvegardée');

            return $this->redirectToRoute('app_hexagoncrud_editweight', ['pk' => $obj->getPk()]);
        }

        return $this->render('hex/set_create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tileset/weight/{pk}")
     */
    public function editWeight(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
                ->add('collection', CollectionType::class, [
                    'entry_type' => WeightType::class,
                ])
                ->add('edit', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
            $this->addFlash('success', 'Poids sauvegardés');

            return $this->redirectToRoute('app_hexagoncrud_editanchor', ['pk' => $arrang->getPk()]);
        }

        return $this->render('hex/set_weight.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tileset/anchor/{pk}")
     */
    public function editAnchor(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
                ->add('collection', CollectionType::class, [
                    'entry_type' => AnchorType::class,
                ])
                ->add('edit', SubmitType::class)
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
     * @Route("/tileset/rotation/{pk}")
     */
    public function editRotation(string $pk, Request $request): Response
    {
        $arrang = $this->tileRepo->load($pk);

        $form = $this->createFormBuilder($arrang)
                ->add('collection', CollectionType::class, [
                    'entry_type' => RotationType::class,
                ])
                ->add('edit', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->tileRepo->save($form->getData());
            $this->addFlash('success', 'Rotations sauvegardées');

            return $this->redirectToRoute('app_hexagoncrud_show', ['pk' => $arrang->getPk()]);
        }

        return $this->render('hex/set_rotation.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tileset/show/{pk}")
     */
    public function show(string $pk): Response
    {
        $fac = new Factory();
        /** @var TileArrangement $arrang */
        $arrang = $this->tileRepo->load($pk);

        $base = $fac->buildEigenTileBase($arrang);

        return $this->render('hex/showbase.html.twig', ['tileset' => $arrang, 'eigenbase' => $base]);
    }

    /**
     * @Route("/tileset/generate/{pk}")
     */
    public function generate(string $pk): Response
    {
        // srand(12);
        $size = 40;

        $fac = new Factory();
        $arrang = $this->tileRepo->load($pk);

        $base = $fac->buildEigenTileBase($arrang);
        $wf = $fac->buildWaveFunction($size, $base);
        $battlemap = $fac->buildBattlemap($size, $arrang, $base);

        while ($wf->newIterate()) {
            //      $this->printWave($wf, $output);
        }
        $wf->retryConflict();
        //   $wf->retryHarderConflict();

        $wf->dump($battlemap);

        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

    /**
     * @Route("/tileset/voronoi")
     */
    public function voronoi(): Response
    {
        $size = 50;
        $map = new HexaMap($size);

        $battlemap = new BattlemapSvg();
        $root = $battlemap->createElementNS(TileSvg::svgNS, 'svg');
        $root->setAttribute('viewBox', "0 0 $size $size");
        $battlemap->appendChild($root);

        $defs = $battlemap->createElementNS(TileSvg::svgNS, 'defs');
        $root->appendChild($defs);

        $svg = new TileSvg();
        $svg->load($this->getParameter('kernel.project_dir') . '/templates/hex/tile/empty.svg');
        $item = $svg->getTile();
        $imported = $battlemap->importNode($item, true);
        $defs->appendChild($imported);

        for ($k = 0; $k < 500; $k++) {
            $cell = new HexaCell();
            $cell->uid = $k;
            $map->setCell([rand(0, $size - 1), rand(0, $size - 1)], $cell);
        }

        while ($map->iterateNeighborhood()) {
            // nothing
        }

        // map
        $item = $battlemap->createElementNS(TileSvg::svgNS, 'g');
        $item->setAttribute('id', 'ground');
        $root->appendChild($item);

        $map->dump($battlemap);
        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

}
