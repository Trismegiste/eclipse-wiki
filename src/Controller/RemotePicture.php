<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Service\MediaWiki;
use App\Service\MwImageCache;
use DOMDocument;
use DOMNode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for accessing remote file on the MediaWiki
 */
class RemotePicture extends AbstractController
{

    /**
     * Show image from MediaWiki
     * @Route("/remote/get", methods={"GET"})
     */
    public function read(Request $request, MwImageCache $cache): \Symfony\Component\HttpFoundation\Response
    {
        $url = $request->query->get('url');
        return $cache->get(rawurldecode($url));
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
            $content = strip_tags($mw->renderGallery($listing), '<a><div><figure><img>');
            $doc = new DOMDocument("1.0", "utf-8");
            $doc->loadXML($content);
            $xpath = new \DOMXpath($doc);
            $elements = $xpath->query('//a[@class="image"]/img');
            foreach ($elements as $img) {
                /** @var DOMNode $img */
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

    /**
     * Pushes a picture (from the remote MediaWiki) to player screen
     * @Route("/remote/push/{title}", methods={"POST"})
     */
    public function push(string $title): JsonResponse
    {
        
    }

}
