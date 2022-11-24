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
     * Generates the Handout PDF and prints a QR Code for player
     * @Route("/handout/qrcode/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function qrcode(Handout $vertex, \App\Service\DocumentBroadcaster $broadcast): Response
    {
        $title = sprintf("Handout-%s.pdf", $vertex->getTitle());
        $html = $this->renderView('handout/pc_export.pdf.twig', ['vertex' => $vertex]);
        $lan = $broadcast->getExternalLinkForGeneratedPdf($title, $html, self::pdfOptions);

        $this->addFlash('success', 'PDF généré');

        return $this->render('player/getdocument.html.twig', ['url_cast' => $lan]);
    }

}
