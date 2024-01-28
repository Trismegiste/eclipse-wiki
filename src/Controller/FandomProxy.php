<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\MediaWikiPage;
use App\Service\MediaWiki;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Description of FandomProxy
 *
 * @author florent
 */
#[Route('/fandom')]
class FandomProxy extends AbstractController
{

    public function __construct(protected MediaWiki $wiki)
    {
        
    }

    #[Route("/search", methods: ['GET'])]
    public function fandom(Request $request,): Response
    {
        $form = $this->createFormBuilder()
                ->add('query')
                ->add('search', SubmitType::class)
                ->setMethod('GET')
                ->getForm();

        $result = [];

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->wiki->searchPageByName($form['query']->getData());
        }

        return $this->render('fandom/search.html.twig', [
                    'title' => 'fandom',
                    'form' => $form->createView(),
                    'result' => $result
        ]);
    }

    #[Route("/show/{id}", methods: ['GET'])]
    public function show(int $id): Response
    {
        $page = $this->wiki->getWikitextById($id);
        $this->purifyRemoteWikitext($page);

        return $this->render('fandom/show.html.twig', ['page' => $page]);
    }

    protected function purifyRemoteWikitext(MediaWikiPage $page): void
    {
        $page->content = preg_replace(
                [
                    '#\[\[Fichier\:([^\]]+)\]\]#',
                    '#\[\[Catégorie\:([^]]+)\]\]#',
                    '#\{\{([^\]]+)\}\}#',
                    '#\[\[([^\]\|]+)\]\]#',
                    '#\[\[[^\|\]]+\|([^\]]+)\]\]#',
                ],
                [
                    '',
                    '[https://eclipse-savage.fandom.com/fr/wiki/Cat%C3%A9gorie:$1 Catégorie $1]',
                    '',
                    "'''\$1'''",
                    "'''\$1'''",
                ],
                $page->content);
    }

}
