<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Handout;
use App\Entity\Vertex;
use App\Form\HandoutType;
use App\Repository\VertexRepository;
use App\Service\ObjectPushFactory;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Handout
 */
class HandoutCrud extends GenericCrud
{

    const pdfOptions = ['page-size' => 'A5'];

    protected $knpPdf;

    public function __construct(VertexRepository $repo, Pdf $knpSnappyPdf)
    {
        parent::__construct($repo);
        $this->knpPdf = $knpSnappyPdf;
    }

    protected function createEntity(string $title): Vertex
    {
        return new Handout($title);
    }

    /**
     * Creates a Handout
     * @Route("/handout/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(HandoutType::class, 'handout/create.html.twig', $request);
    }

    /**
     * Edits a Handout
     * @Route("/handout/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(HandoutType::class, 'handout/edit.html.twig', $pk, $request);
    }

    /**
     * Send a Handout PDF to bluetooth
     * @Route("/handout/send/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function send(string $pk, ObjectPushFactory $fac): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $path = \join_paths($this->getParameter('kernel.cache_dir'), 'pdf', $this->getFilenameAfter($vertex));
        $html = $this->renderView('handout/pc_export.html.twig', ['vertex' => $vertex]);
        $this->knpPdf->generateFromHtml($html, $path, self::pdfOptions, true);
        $fac->send($path);
        $this->addFlash('success', 'PDF envoyé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

    protected function getFilenameAfter(Handout $vertex): string
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', sprintf("Handout-%s.pdf", $vertex->getTitle()));
    }

}
