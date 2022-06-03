<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\MediaWiki;
use App\Service\MwImageCache;
use App\Service\PlayerCastCache;
use DOMDocument;
use DOMNode;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function join_paths;

/**
 * Controller for accessing remote file on the MediaWiki
 */
class RemotePicture extends AbstractController
{

    protected $remoteStorage;

    public function __construct(MwImageCache $mw)
    {
        $this->remoteStorage = $mw;
    }

    /**
     * Image search against the remote MediaWiki
     * @Route("/remote/search", methods={"GET"})
     */
    public function search(Request $request, MediaWiki $mw): Response
    {
        $form = $this->createFormBuilder()
                ->add('query')
                ->add('search', SubmitType::class)
                ->setMethod('GET')
                ->getForm();

        $extract = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $mw->searchImage($form['query']->getData());
            $extract = $mw->extractUrlFromGallery($mw->renderGallery($listing));
        }

        return $this->render('picture/search.html.twig', ['form' => $form->createView(), 'gallery' => $extract]);
    }

    /**
     * Show image from MediaWiki
     * @Route("/remote/get", methods={"GET"})
     */
    public function read(Request $request): Response
    {
        $url = rawurldecode($request->query->get('url'));
        return $this->remoteStorage->get($url);
    }

    /**
     * Pushes a picture (from the remote MediaWiki) to player screen
     * @Route("/remote/push", methods={"POST"})
     */
    public function push(Request $request, PlayerCastCache $cache): JsonResponse
    {
        $url = rawurldecode($request->query->get('url'));
        $picture = $cache->slimPictureForPush($this->remoteStorage->download($url));

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $picture->getPathname()]);
    }

}
