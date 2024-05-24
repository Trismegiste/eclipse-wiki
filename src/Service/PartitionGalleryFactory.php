<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\GalleryItem;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Build a gallery of Vertices, group by category
 */
class PartitionGalleryFactory
{

    protected $getIcon;

    public function __construct(protected UrlGeneratorInterface $routing, Environment $twig)
    {
        $this->getIcon = $twig->getFunction('vertex_icon')->getCallable();
    }

    public function create(iterable $cursor): array
    {
        $gallery = [];
        foreach ($cursor as $vertex) {
            /** @var Vertex $vertex */
            $entry = new GalleryItem($vertex, ($this->getIcon)($vertex));
            $entry->classname = 'square';

            if ($vertex instanceof Transhuman && !empty($vertex->tokenPic)) {
                $entry->push = $entry->thumb = $this->routing->generate('app_profilepicture_unique', ['pk' => $vertex->getPk()]);
                $entry->classname = 'pure-img';
            } else {
                if (count($entry->picture)) {
                    $firstEntry = reset($entry->picture);
                    $entry->thumb = $this->routing->generate('get_picture', ['title' => $firstEntry]);
                    $entry->push = $this->routing->generate('app_picture_push', ['title' => $firstEntry]);
                }
            }
            $gallery[$vertex->getCategory()][] = $entry;
        }
        // transhuman and place categories in front of the array
        $this->moveKeyInFront($gallery, 'place');
        $this->moveKeyInFront($gallery, 'transhuman');

        return $gallery;
    }

    protected function moveKeyInFront(&$assoc, string $key): void
    {
        if (key_exists($key, $assoc)) {
            $assoc = array_merge([$key => $assoc[$key]], $assoc);
        }
    }

}
