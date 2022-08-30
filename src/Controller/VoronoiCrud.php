<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Vertex;
use App\Voronoi\MapBuilder;
use App\Voronoi\MapConfig;
use App\Voronoi\MapConfigType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class VoronoiCrud extends GenericCrud
{

    /**
     * @Route("/voronoi/generate/{pk}/{fog}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function generate(string $pk, MapBuilder $builder, bool $fog = true): Response
    {
        $config = $this->repository->load($pk);
        $map = $builder->create($config, $fog);

        return new StreamedResponse(function () use ($builder, $map, $fog) {
                    $builder->dumpSvg($map, $fog);
                }, Response::HTTP_OK, ['content-type' => 'image/svg+xml']);
    }

    /**
     * Creates a battlemap config
     * @Route("/voronoi/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(MapConfigType::class, 'voronoi/create.html.twig', $request);
    }

    /**
     * Edits a battlemap config with direct view (loop)
     * @Route("/voronoi/edit/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function edit(string $pk, Request $request): Response
    {
        $resp = $this->handleEdit(MapConfigType::class, 'voronoi/edit.html.twig', $pk, $request);
        if ($resp->isRedirection()) {
            return $this->redirectToRoute('app_voronoicrud_edit', ['pk' => $pk]);
        }

        return $resp;
    }

    protected function createEntity(string $title): Vertex
    {
        return new MapConfig($title);
    }

    /**
     * Show map to run it on the fly
     * @Route("/voronoi/running/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function running(string $pk, Request $request): Response
    {
        $config = $this->repository->load($pk);
        $url = $this->generateUrl('app_voronoicrud_generate', ['pk' => $pk]);

        return $this->render('map/running.html.twig', ['title' => 'on the fly ' . $config->getTitle(), 'img' => $url]);
    }

    /**
     * Attach the generated map to a Place entity
     * @Route("/voronoi/attachplace/{pk}", methods={"GET","PATCH"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function attachPlace(string $pk, Request $request, MapBuilder $builder, \App\Service\Storage $storage, \Symfony\Contracts\Translation\TranslatorInterface $translator): Response
    {
        /** @var \App\Voronoi\MapConfig $config */
        $config = $this->repository->load($pk);

        $form = $this->createFormBuilder()
                ->add('place', \App\Form\Type\PlaceChoiceType::class, [
                    'placeholder' => '-- Create New --',
                    'required' => false
                ])
                ->add('Attacher', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
                ->setMethod('PATCH')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $place = $form['place']->getData();
            $newPlace = empty($place);

            if ($newPlace) {
                $place = new \App\Entity\Place(sprintf('Map-%s %s-%d×%d %d',
                                $config->getTitle(),
                                $translator->trans($config->container->getName()),
                                $config->side,
                                $config->side,
                                $config->seed));
                $this->repository->save($place);
            }

            $filename = 'map-' . $place->getPk() . '.svg';
            $map = $builder->create($config);
            $builder->save($map, \join_paths($storage->getRootDir(), $filename));
            $place->battleMap = $filename;
            $this->repository->save($place);

            $this->addFlash('success', 'Plan sauvegardé dans ' . $place->getTitle());

            return $this->redirectToRoute($newPlace ? 'app_vertexcrud_rename' : 'app_vertexcrud_show', ['pk' => $place->getPk()]);
        }

        return $this->render('voronoi/attachplace.html.twig', ['vertex' => $config, 'form' => $form->createView()]);
    }

}
