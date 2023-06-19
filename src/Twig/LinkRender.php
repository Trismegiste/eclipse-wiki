<?php

/*
 * Eclipse Wiki
 */

namespace App\Twig;

use App\Repository\VertexRepository;
use Mike42\Wikitext\HtmlRenderer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * LinkRender is an implementation of a WikiText renderer with internal links and local files
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
        $picture = $this->routing->generate('get_picture', ['title' => $info['url']]);
        $info['thumb'] = $picture;
        $info['url'] = $this->routing->generate('app_picture_push', ['title' => $info['url']]);
        $info['thumbnail'] = true;
        $info['caption'] = false;
        $info['class'] = 'pushable';

        return $info;
    }

    public function getInternalLinkInfo($info): array
    {
        if (!$info['external'] && !empty($info['title'])) {
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
