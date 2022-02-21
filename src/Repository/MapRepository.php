<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Place;
use App\MapLayer\IteratorDecorator;
use App\MapLayer\ThumbnailMap;
use App\Service\Storage;
use Iterator;
use MongoDB\BSON\Binary;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Trismegiste\MapGenerator\SvgPrintable;
use function join_paths;

/**
 * Repository for battle maps
 */
class MapRepository
{

    protected $templateDir;
    protected $storage;
    protected $mongo;

    public function __construct(string $template, Storage $storage, VertexRepository $repo)
    {
        $this->templateDir = $template;
        $this->storage = $storage;
        $this->mongo = $repo;
    }

    /**
     * Gets form parameters from one battle map template
     * @param string $key the battle map key (= filename without extension)
     * @return array
     */
    public function getTemplateParam(string $key): array
    {
        $thumb = new ThumbnailMap(new SplFileInfo(join_paths($this->templateDir, $key . '.svg')));
        $data = $thumb->getFormData();
        unset($thumb);

        return $data;
    }

    /**
     * Gets all thumbnails for battle map Templates
     * @return Iterator
     */
    public function findAll(): Iterator
    {
        $template = new Finder();
        $it = $template->in($this->templateDir)
                ->files()
                ->name('*.svg')
                ->getIterator();

        return new IteratorDecorator($it);
    }

    /**
     * Writes the battle map SVG to the disk and update the Place vertex (if provided)
     * @param SvgPrintable $map
     * @param string $filename
     * @param Place|null $place
     */
    public function writeAndSave(SvgPrintable $map, string $filename, ?Place $place): void
    {
        $path = \join_paths($this->storage->getRootDir(), $filename);

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

    /**
     * Deletes battle map SVG files that are not linked to a Place vertex
     */
    public function deleteOrphanMap(): void
    {
        $iter = $this->mongo->search([
            '__pclass' => new Binary(Place::class, Binary::TYPE_USER_DEFINED),
            'battleMap' => ['$ne' => null]
                ], ['content']);

        $place = [];
        foreach ($iter as $item) {
            $place[] = $item->battleMap;
        }

        $scan = $this->storage->searchByName('*.svg');

        foreach ($scan as $svg) {
            if (!in_array($svg->getFilename(), $place)) {
                $this->storage->delete($svg->getBasename());
            }
        }
    }

}
