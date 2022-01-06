<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Loveletter;
use App\Entity\Vertex;
use App\Form\LoveletterPcChoice;
use App\Form\LoveletterType;
use App\Repository\VertexRepository;
use App\Service\ObjectPushFactory;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD for Loveletter
 */
class LoveletterCrud extends GenericCrud
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
        return new Loveletter($title);
    }

    /**
     * Creates a Love letter
     * @Route("/loveletter/create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        return $this->handleCreate(LoveletterType::class, 'loveletter/create.html.twig', $request);
    }

    /**
     * Edits a Love letter
     * @Route("/loveletter/edit/{pk}", methods={"GET","PUT"})
     */
    public function edit(string $pk, Request $request): Response
    {
        return $this->handleEdit(LoveletterType::class, 'loveletter/edit.html.twig', $pk, $request);
    }

    /**
     * Generate PDF for a Love letter
     * @Route("/loveletter/pdf/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pdf(string $pk): Response
    {
        $vertex = $this->repository->findByPk($pk);

        return new PdfResponse(
                $this->knpPdf->getOutputFromHtml($this->generateHtmlFor($vertex), self::pdfOptions),
                $this->getFilenameAfter($vertex)
        );
    }

    /**
     * Send a Love letter PDF to bluetooth
     * @Route("/loveletter/send/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function send(string $pk, ObjectPushFactory $fac): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $path = \join_paths($this->getParameter('kernel.cache_dir'), 'pdf', $this->getFilenameAfter($vertex));
        $this->knpPdf->generateFromHtml($this->generateHtmlFor($vertex), $path, self::pdfOptions, true);
        $fac->send($path);
        $this->addFlash('success', 'PDF envoyé');

        return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $vertex->getPk()]);
    }

    protected function generateHtmlFor(Loveletter $vertex): string
    {
        return $this->renderView('loveletter/export.pdf.twig', ['vertex' => $vertex]);
    }

    protected function getFilenameAfter(Loveletter $vertex): string
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT', sprintf("Loveletter-%s-%s.pdf", $vertex->player, $vertex->getTitle()));
    }

    /**
     * Selection of the PC for the different resolution of the love letter
     * @Route("/loveletter/select/{pk}", methods={"GET","PUT"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function select(string $pk, Request $request): Response
    {
        return $this->handleEdit(LoveletterPcChoice::class, 'loveletter/select.html.twig', $pk, $request);
    }

}
