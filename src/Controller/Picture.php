<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\PictureUpload;
use App\Repository\VertexRepository;
use App\Service\PictoProvider;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use DateTime;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    public function upload(Request $request, VertexRepository $repository): Response
    {
        $form = $this->createForm(PictureUpload::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->storage->storePicture($data['picture'], $data['filename']);
                $this->addFlash('success', "Upload {$data['filename']} OK");
                if (!empty($data['append_vertex'])) {
                    $vertex = $repository->findByTitle($data['append_vertex']);
                    $vertex->setContent($vertex->getContent() . "\n\n[[file:{$data['filename']}.jpg]]\n");
                    $repository->save($vertex);
                    $this->addFlash('success', "Picture {$data['filename']} append to '{$data['append_vertex']}'");
                }

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
    public function battlemapThumbnail(\App\Entity\Place $place, VertexRepository $repository, Request $request, MapBuilder $builder, \App\Voronoi\SvgDumper $dumper): Response
    {
        // plusieurs possibilités pour migrer la thumbnail :
        // 1 - stocker le BattlemapDocument dans la Place plutôt que dans un fichier JSON dans Storage. Ça permet de récupérer un BattlemapDocument exploitale par SvgDumper
        // ======>>>>>>>> 2 - renderiser seulement l'hexamap qui ne sera pas à jour par rapport au JSON - CHOIX ACTUEL
        // 3 - renderiser côté client avec Babylon une version plus light du fichier (pas de texture)
        // 4 - plutôt que de json_encoder, on peut utiliser toJSON et fromPHP sauf que le client envoie un json sans type dans l'export
        // 5 - faire un SvgDumper qui travaille sur un BattlemapDocument dés-objectifié (sans HexaCell ni MapToken)
        if (is_null($place->battlemap3d)) {
            throw $this->createNotFoundException();
        }

        // folder for caching :
        $cacheDir = join_paths($this->getParameter('kernel.cache_dir'), PlayerCastCache::subDir);
        $targetName = join_paths($cacheDir, 'tmp-' . $place->getPk());

        // managing HTTP Cache
        if (file_exists("$targetName.jpg")) {
            $response = new BinaryFileResponse("$targetName.jpg");
            $response->setLastModified(new DateTime('@' . $place->getPk()->getTimestamp()));
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $output = fopen("$targetName.html", 'w');
        $map = $builder->create($place->voronoiParam);
        $doc = new \App\Entity\BattlemapDocument();
        $map->dumpMap($doc);

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

        ob_start();
        $dumper->flush($doc);
        fwrite($output, ob_get_clean());

        fwrite($output, '</body></html>');
        fclose($output);

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

        $response = new BinaryFileResponse("$targetName.jpg");
        $response->setLastModified(new DateTime('@' . $place->getPk()->getTimestamp()));

        return $response;
    }

    /**
     * Show token from storage
     * @Route("/token/get", methods={"GET"})
     */
    public function readToken(Request $request, VertexRepository $repo, MapBuilder $builder): Response
    {
        $title = $request->query->get('title');
        $npc = $repo->findByTitle($title);
        $pic = $this->storage->getFileInfo($npc->tokenPic);

        return new StreamedResponse(function () use ($builder, $pic) {
                    $builder->dumpTokenFor($pic);
                }, 200, ['content-type' => 'image/svg+xml']);
    }

    /**
     * Show pictogram from folder. Returns a SVG fragment
     * @Route("/picto/get", methods={"GET"})
     */
    public function readPictogram(Request $request, PictoProvider $provider): Response
    {
        $title = $request->query->get('title');
        return new Response($provider->getSvg($title), 200, ['content-type' => 'image/svg+xml']);
    }

}
