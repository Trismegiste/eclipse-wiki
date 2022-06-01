<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Ali;
use App\Entity\Freeform;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use App\Service\NetTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

/**
 * Description of GmHelper
 */
class GmHelper extends AbstractController
{

    /**
     * Landing page
     * @Route("/", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('landing.html.twig');
    }

    /**
     * Names generator
     * @Route("/gm/name/{card}", methods={"GET"})
     */
    public function nameGenerate(int $card = 15): Response
    {
        $repo = new RandomizerDecorator(new FileRepository());
        $config = $this->getParameter('generator');
        $listing = [];

        foreach (['female', 'male'] as $gender) {
            foreach ($config as $idx => $combo) {
                for ($k = 0; $k < $card; $k++) {
                    $listing[$gender][$idx][] = $repo->getRandomGivenNameFor($gender, $combo[0]) . ' ' . $repo->getRandomSurnameFor($combo[1]);
                }
            }
        }

        return $this->render('random_name.html.twig', ['listing' => $listing]);
    }

    /**
     * Generates a QR Code for external initiative tracker
     * @Route("/tracker", methods={"GET"})
     */
    public function tracker(VertexRepository $repo): Response
    {
        $listing = $repo->findByClass([Ali::class, Freeform::class, Transhuman::class]);

        return $this->render('tracker.html.twig', ['listing' => $listing]);
    }

    /**
     * Creates a QR Code for the link to player screen
     * @Route("/broadcast/qrcode", methods={"GET"})
     */
    public function qrCode(NetTools $ntools): Response
    {
        $url = $this->generateUrl('app_playercast_view', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $lan = preg_replace('#//localhost#', '//' . $ntools->getLocalIp(), $url); // @todo hardcoded config

        return $this->render('player/qrcode.html.twig', ['url_cast' => $lan]);
    }

    /**
     * Image search
     * @Route("/mediawiki/picture", methods={"GET"})
     */
    public function picture(\Symfony\Component\HttpFoundation\Request $request, \App\Service\MediaWiki $mw): Response
    {
        $form = $this->createFormBuilder()
            ->add('query')
            ->add('search', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
            ->setMethod('GET')
            ->getForm();

        $thumb = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $mw->searchImage($form['query']->getData());
            $content = $mw->renderGallery($listing);
            $doc = new \DOMDocument("1.0", "utf-8");
            $doc->loadXML($content);
            $xpath = new \DOMXpath($doc);
            $elements = $xpath->query("//img");
            foreach ($elements as $img) {
                $thumb[] = $img->attributes->getNamedItem('src')->value;
            }
        }

        return $this->render('picture/search.html.twig', ['form' => $form->createView(), 'gallery' => $thumb]);
    }

}
