<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Place;
use App\MapLayer\IteratorDecorator;
use App\MapLayer\ThumbnailMap;
use Iterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Trismegiste\MapGenerator\RpgMap;
use function join_paths;

/**
 * Repository for battle maps
 */
class MapRepository
{

    protected $templateDir;
    protected $uploadDir;
    protected $mongo;

    public function __construct(string $template, string $upload, VertexRepository $repo)
    {
        $this->templateDir = $template;
        $this->uploadDir = $upload;
        $this->mongo = $repo;
    }

    public function getTemplateParam(string $key): array
    {
        $thumb = new ThumbnailMap(new SplFileInfo(join_paths($this->templateDir, $key . '.svg')));
        $data = $thumb->getFormData();
        unset($thumb);

        return $data;
    }

    public function findAll(): Iterator
    {
        $template = new Finder();
        $it = $template->in($this->templateDir)
            ->files()
            ->name('*.svg')
            ->getIterator();

        return new IteratorDecorator($it);
    }

    public function writeAndSave(RpgMap $map, string $filename, ?Place $place): void
    {
        $path = \join_paths($this->uploadDir, $filename);

        // persist file
        $ptr = fopen($path, 'w');
        ob_start(function (string $buffer) use ($ptr) {
            fwrite($ptr, $buffer);
        });
        $map->printSvg();
        ob_end_clean();

        // persist vertex if any
        if (!empty($place)) {
            $place->battleMap = $filename;
            $this->mongo->save($place);
        }
    }

}
