<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Character;
use App\Entity\Vertex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Build a gallery of Vertices, group by category
 */
class PartitionGalleryFactory
{

    public function __construct(protected UrlGeneratorInterface $routing, protected Environment $twig)
    {
        
    }

    public function create(iterable $cursor): array
    {
        $gallery = [];
        foreach ($cursor as $vertex) {
            /** @var Vertex $vertex */
            $pic = $vertex->extractPicture();
            // @todo this should be a class
            $entry = [
                'pk' => $vertex->getPk(),
                'title' => $vertex->getTitle(),
                'picture' => $pic,
                'icon' => $this->twig->getFunction('vertex_icon')->getCallable()($vertex),
                'thumb' => null,
                'push' => null,
                'classname' => 'square'
            ];
            if ($vertex instanceof Character && !empty($vertex->tokenPic)) {
                $entry['thumb'] = $this->routing->generate('app_profilepicture_unique', ['pk' => $vertex->getPk()]);
                $entry['push'] = $entry['thumb'];
                $entry['classname'] = 'pure-img';
            } else {
                if (count($pic)) {
                    $firstEntry = array_shift($pic);
                    $entry['thumb'] = $this->routing->generate('get_picture', ['title' => $firstEntry]);
                    $entry['push'] = $this->routing->generate('app_picture_push', ['title' => $firstEntry]);
                }
            }
            $gallery[$vertex->getCategory()][] = $entry;
        }
        // transhuman and place categories in front of the array
        if (key_exists('place', $gallery)) {
            $gallery = array_merge(['place' => $gallery['place']], $gallery);
        }
        if (key_exists('transhuman', $gallery)) {
            $gallery = array_merge(['transhuman' => $gallery['transhuman']], $gallery);
        }

        return $gallery;
    }

}
