<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Form\MapConfigType;
use App\Form\PlaceType;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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
     * Page for the battlemap
     * @Route("/place/battlemap/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function battlemap(string $pk, Storage $storage): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $svg = file_get_contents($storage->getFileInfo($vertex->battleMap)->getPathname());

        return $this->render('map/running.html.twig', ['title' => $vertex->getTitle(), 'svg' => $svg]);
    }

    /**
     * Redirection to default NPC or Profile on the fly
     * @Route("/place/npc/{title}", methods={"GET"})
     */
    public function npcShow(string $title): Response
    {
        $npc = $this->repository->findByTitle($title);

        if (is_null($npc) || (!$npc instanceof Transhuman)) {
            throw new NotFoundHttpException("$title is not a Transhuman");
        }

        if (!empty($npc->surnameLang)) {
            return $this->redirectToRoute('app_profilepicture_generateonthefly', ['pk' => $npc->getPk()]);
        } else {
            $this->addFlash('error', "Cannot generate a profile from '$title' since its surname language is not defined");

            return $this->redirectToRoute('app_npcgenerator_info', ['pk' => $npc->getPk()]);
        }
    }

    /**
     * Creates a Place child from the current Place
     * @Route("/place/child/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function child(string $pk, Request $request): Response
    {
        $place = $this->repository->findByPk($pk);
        if (is_null($place) || (!$place instanceof Place)) {
            throw new NotFoundHttpException("Vertex $pk is not a Place");
        }

        $title = $place->getTitle();
        $child = clone $place;
        $child->setTitle("Lieu enfant dans $title");
        $child->setContent("Localisé sur [[$title]]");
        $child->battleMap = null;
        $form = $this->createForm(PlaceType::class, $child);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
        }

        return $this->render('place/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates or Edits a voronoi Map in the current Place
     * @Route("/place/voronoi/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function voronoiCreate(string $pk, Request $request): Response
    {
        $place = $this->repository->findByPk($pk);
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', MapConfigType::class)
                ->add('generate', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vertex = $form->getData();
            $this->repository->save($vertex);

            return $this->redirectToRoute('app_placecrud_voronoicreate', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/place/voronoi/generate/{pk}/{fog}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function voronoiGenerate(MapBuilder $builder, string $pk, bool $fog = true): Response
    {
        $place = $this->repository->load($pk);
        $config = $place->voronoiParam;

        try {
            $map = $builder->create($config);

            return new StreamedResponse(function () use ($map, $fog, $builder) {
                        $builder->dumpSvg($map, $fog);
                    }, Response::HTTP_OK, ['content-type' => 'image/svg+xml']);
        } catch (RuntimeException $e) {
            return new BinaryFileResponse($this->getParameter('twig.default_path') . '/voronoi/fail.svg', 200, [], false, null, false, false);
        }
    }

    /**
     * Edits tiles texturing of a map with direct view (loop)
     * @Route("/place/voronoi/texture/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function voronoiTexture(string $pk, Request $request): Response
    {
        $place = $this->repository->load($pk);
        $form = $this->createFormBuilder($place)
                ->add('voronoiParam', \App\Form\MapTextureType::class, ['tileset' => 'habitat'])
                ->add('texture', SubmitType::class)
                ->setMethod('PUT')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($form->getData());

            return $this->redirectToRoute('app_placecrud_voronoitexture', ['pk' => $pk]);
        }

        return $this->render('voronoi/edit.html.twig', ['form' => $form->createView()]);
    }

}
