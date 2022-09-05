<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Service\PlayerCastCache;
use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use function join_paths;

/**
 * CRUD for battlemap
 */
class MapCrud extends AbstractController
{

    /**
     * AJAX Pushes the modified SVG to websocket server
     * @Route("/map/broadcast", methods={"POST"})
     */
    public function pushPlayerView(Request $request): JsonResponse
    {
        $playerDir = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir);
        /** @var UploadedFile $svgContent */
        $svgContent = $request->files->get('svg')->move($playerDir, 'tmp-map.svg');
        // the moving was necessary because wkhtmltoimage fails to load a SVG file without extension
        $target = join_paths($playerDir, 'tmp-map.png');
        $process = new Process([
            'wkhtmltoimage',
            '--quality', 50,
            '--crop-w', MapBuilder::defaultSizeForWeb,
            $svgContent->getPathname(),
            $target
        ]);
        $process->mustRun();

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $target]);
    }

}
