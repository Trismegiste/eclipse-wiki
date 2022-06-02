<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\DocumentBroadcaster;
use App\Service\MediaWiki;
use App\Service\WebsocketPusher;
use DOMDocument;
use Paragi\PhpWebsocket\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ctrl for WebSocket-controled Player Screen
 */
class PlayerCast extends AbstractController
{

    protected $pusher;

    public function __construct(WebsocketPusher $fac)
    {
        $this->pusher = $fac;
    }

    /**
     * The actual player screen updated with websocket
     * @Route("/player/view", methods={"GET"})
     */
    public function view(): Response
    {
        return $this->render('player/view.html.twig', ['host' => $this->pusher->getUrl()]);
    }

    /**
     * Returns a generated document
     * @Route("/player/getdoc/{filename}", methods={"GET"})
     */
    public function getDocument(string $filename, DocumentBroadcaster $broad): Response
    {
        return $broad->createResponseForFilename($filename);
    }

    //  /!\ -- Big security breach : internally called ONLY -- /!\
    // DO NOT EXPOSE THIS CONTROLLER PUBLICLY
    public function internalPushFile(string $pathname): JsonResponse
    {
        try {
            $ret = $this->pusher->push(json_encode([
                'file' => $pathname,
                'action' => 'pictureBroadcast'
            ]));

            return new JsonResponse(['level' => 'success', 'message' => $ret], Response::HTTP_OK);
        } catch (ConnectionException $e) {
            return new JsonResponse(['level' => 'error', 'message' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Image search against the remote MediaWiki
     * @Route("/remote/search", methods={"GET"})
     */
    public function remoteSearch(Request $request, MediaWiki $mw): Response
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
            $content = strip_tags($mw->renderGallery($listing), '<a><div><figure><img>');
            $doc = new DOMDocument("1.0", "utf-8");
            $doc->loadXML($content);
            $xpath = new \DOMXpath($doc);
            $elements = $xpath->query('//a[@class="image"]/img');
            foreach ($elements as $img) {
                /** @var \DOMNode $img */
                $thumbnail = $img->attributes->getNamedItem('src')->value;
                if (0 === strpos($thumbnail, 'http')) {
                    $extract[] = (object) [
                            'thumbnail' => $thumbnail,
                            'original' => $img->parentNode->attributes->getNamedItem('href')->value
                    ];
                }
            }
        }

        return $this->render('picture/search.html.twig', ['form' => $form->createView(), 'gallery' => $extract]);
    }

}
