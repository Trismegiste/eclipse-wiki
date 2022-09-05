<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\PictureUpload;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use function join_paths;

/**
 * Controller for pictures
 */
class Picture extends AbstractController
{

    protected Storage $storage;

    public function __construct(Storage $store)
    {
        $this->storage = $store;
    }

    /**
     * Ajax for searching local images
     * @Route("/picture/search", methods={"GET"})
     */
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        $it = $this->storage->searchByTitleContains($title);

        $choice = [];
        foreach ($it as $fch) {
            $choice[] = $fch->getBasename();
        }

        return new JsonResponse($choice);
    }

    /**
     * Show image from storage
     * @Route("/picture/get/{title}", name="get_picture", methods={"GET"})
     */
    public function read(string $title): Response
    {
        return $this->storage->createResponse($title);
    }

    /**
     * Pushes a picture (from the Storage) to player screen
     * @Route("/picture/push/{title}", methods={"POST"})
     */
    public function push(string $title, PlayerCastCache $cache): JsonResponse
    {
        $picture = $cache->slimPictureForPush($this->storage->getFileInfo($title));

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $picture->getPathname()]);
    }

    /**
     * Upload a new picture
     * @Route("/picture/upload", methods={"GET","POST"})
     */
    public function upload(Request $request): Response
    {
        $form = $this->createForm(PictureUpload::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->storage->storePicture($data['picture'], $data['filename']);
                $this->addFlash('success', "Upload {$data['filename']} OK");

                return $this->redirectToRoute('app_picture_upload');
            } catch (RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $listing = $this->storage->searchLastPicture();

        return $this->render('picture/upload.html.twig', [
                    'form' => $form->createView(),
                    'last' => $listing
        ]);
    }

    /**
     * Returns a pixelized thumbnail for the vector battlemap linked to the Place given by its pk
     * @Route("/battlemap/thumbnail/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function battlemapThumbnail(string $pk): Response
    {
        $place = $this->repository->findByPk($pk);

        $battlemapSvg = $this->storage->getFileInfo($place->battleMap);
        // folder for caching :
        $cacheDir = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir);
        $targetName = join_paths($cacheDir, $battlemapSvg->getBasename('.svg'));

        $output = fopen("$targetName.html", 'w');
        $source = fopen($battlemapSvg->getPathname(), 'r');
        $widthForMap = MapBuilder::defaultSizeForWeb;
        fwrite(
                $output,
                <<<YOLO
<html>
<head>
    <style>
        #gm-fogofwar {
            display: none;
        }
    </style>
</head>
<body style="width:$widthForMap">
YOLO
        );
        while ($buf = fread($source, 10000)) {
            fwrite($output, $buf);
        }
        fwrite($output, '</body></html>');
        fclose($output);
        fclose($source);

        $matrixing = new Process([
            'wkhtmltoimage',
            "$targetName.html",
            "$targetName.png"
        ]);
        $matrixing->mustRun();

        $convert = new Process([
            'convert',
            "$targetName.png",
            '-resize', 300,
            '-quality', 60,
            "$targetName.jpg"
        ]);
        $convert->mustRun();

        return new BinaryFileResponse("$targetName.jpg");
    }

}
