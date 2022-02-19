<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\MapLayer\ThumbnailMap;
use SplFileInfo;
use function join_paths;

/**
 * Description of MapRepository
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

}
