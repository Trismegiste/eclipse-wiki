<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Repository\VertexRepository;
use Mike42\Wikitext\HtmlRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of LinkRender
 */
class LinkRender extends HtmlRenderer
{

    protected $routing;
    protected $repository;

    public function __construct(UrlGeneratorInterface $routing, VertexRepository $repository, \App\Service\LocalInterwiki $wiki)
    {
        parent::__construct($wiki);
        $this->routing = $routing;
        $this->repository = $repository;
    }

    public function getImageInfo($info): array
    {
        return $info;
    }

    public function getInternalLinkInfo($info): array
    {
        if (!$info['external']) {
            $info['url'] = $this->routing->generate('app_wiki', ['title' => $info['title']]);
            $info['exists'] = $this->documentExists($info['title']);
        }

        return $info;
    }

    protected function documentExists(string $title): bool
    {
        return (bool) $this->repository->findByTitle($title);
    }

}
