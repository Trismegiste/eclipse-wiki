<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\BattlemapDocument;
use App\Entity\Place;
use App\Form\AppendPictureUpload;
use App\Form\MissingPictureUpload;
use App\Repository\VertexRepository;
use App\Service\PictoProvider;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Voronoi\SvgDumper;
use DateTime;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for pictures
 */
class Picture extends AbstractController
{

    public function __construct(protected Storage $storage)
    {
        
    }

    /**
     * Ajax for searching local images
     */
    #[Route('/picture/search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $title = $request->query->get('q', '');
        $limit = $request->query->get('limit', 10);
        $it = $this->storage->searchPictureByTitleContains($title);

        $choice = [];
        $counter = 0;
        foreach ($it as $fch) {
            $choice[] = $fch->getBasename();
            if ($counter > $limit) {
                break;
            }
            $counter++;
        }

        return new JsonResponse($choice);
    }

    /**
     * Show image from storage
     */
    #[Route('/picture/get/{title}', methods: ['GET'], name: 'get_picture')]
    public function read(string $title): Response
    {
        return $this->storage->createResponse($title);
    }

    /**
     * Pushes a picture (from the Storage) to player screen
     */
    #[Route('/picture/push/{title}', methods: ['POST'])]
    public function push(string $title): JsonResponse
    {
        $info = $this->storage->getFileInfo($title);
        $picture = imagecreatefromstring(file_get_contents($info));

        return $this->forward(GmPusher::class . '::internalPushPicture', [
                    'label' => $info->getBasename(),
                    'picture' => $picture
        ]);
    }

    /**
     * Upload a new picture
     */
    #[Route('/picture/upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, VertexRepository $repository): Response
    {
        $form = $this->createForm(AppendPictureUpload::class, null, ['ajax_search' => $this->generateUrl('app_picture_vertexsearch')]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->storage->storePicture($data['picture'], $data['filename']);
                $this->addFlash('success', "Upload {$data['filename']} OK");
                if (!empty($data['append_vertex'])) {
                    $vertex = $data['append_vertex'];
                    $vertex->attachPicture($data['filename'] . '.jpg');
                    $repository->save($vertex);
                    $this->addFlash('success', "Picture {$data['filename']} append to '" . $vertex->getTitle() . "'");
                }

                return $this->redirectToRoute('app_picture_upload');
            } catch (RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $listing = $this->storage->searchLastPicture(10);

        return $this->render('picture/upload.html.twig', [
                    'form' => $form->createView(),
                    'last' => $listing
        ]);
    }

    /**
     * Upload a missing picture
     */
    #[Route('/picture/missing/{title}/upload', methods: ['GET', 'POST'])]
    public function uploadMissing(Request $request, string $title): Response
    {
        $successRedirect = $request->query->get('redirect', $this->generateUrl('app_vertexcrud_list'));

        $form = $this->createForm(MissingPictureUpload::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $filename = pathinfo($title, PATHINFO_FILENAME);
                $this->storage->storePicture($data['picture'], $filename);
                $this->addFlash('success', "Upload $title OK");

                return $this->redirect($successRedirect);
            } catch (RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('form.html.twig', [
                    'form' => $form->createView(),
                    'title' => 'Upload ' . $title
        ]);
    }

    /**
     * Returns a pixelized thumbnail for the vector battlemap linked to the Place given by its pk
     * @todo this ugly code could be replace with Image Magic extension, more on : https://stackoverflow.com/questions/4809194/convert-svg-image-to-png-with-php
     * @todo thumbanailing a battlemap has nothing to do with PlayerCastCache now. Use a new cache for dynamic pictures (could be used also for Profile pic)
     */
    #[Route('/battlemap/thumbnail/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function battlemapThumbnail(Place $place, Request $request, SvgDumper $dumper, PlayerCastCache $playerCache): Response
    {
        if (is_null($place->battlemap3d)) {
            throw $this->createNotFoundException();
        }
        $battlemap = $this->storage->getFileInfo($place->battlemap3d);

        // folder for caching :
        $targetJpeg = $playerCache->createTargetFile('tmp-' . $place->getPk() . '.jpg');

        // managing HTTP Cache
        if ($targetJpeg->isReadable()) {
            $response = new BinaryFileResponse($targetJpeg);
            $response->setLastModified(DateTime::createFromFormat('U', $battlemap->getMTime()));
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $targetSvg = $playerCache->createTargetFile('tmp-' . $place->getPk() . '.svg');
        $output = fopen($targetSvg->getPathname(), 'w');
        $doc = new BattlemapDocument();
        $doc->unserializeFromJson(json_decode(file_get_contents($battlemap->getPathname())), $doc);
        ob_start(function ($buffer) use ($output) {
            fwrite($output, $buffer);
        }, 1e5);
        $dumper->flush($doc);
        ob_end_flush();
        fclose($output);

        // @todo could use stdin/stdout with https://stackoverflow.com/questions/67269725/convert-image-from-one-format-to-another-sent-to-stdout
        // and streamed input with https://stackoverflow.com/questions/67269725/convert-image-from-one-format-to-another-sent-to-stdout
        // with a passthru() ?
        // Use stream at least for input since output should be cached
        $convert = new Process([
            'convert',
            $targetSvg->getPathname(),
            '-resize', 400,
            '-quality', 70,
            $targetJpeg->getPathname()
        ]);
        $convert->mustRun();

        $response = new BinaryFileResponse($targetJpeg);
        $response->setLastModified(DateTime::createFromFormat('U', $battlemap->getMTime()));

        return $response;
    }

    /**
     * Show pictogram from folder. Returns a SVG fragment
     */
    #[Route('/picto/get', methods: ['GET'])]
    public function readPictogram(Request $request, PictoProvider $provider): Response
    {
        $title = $request->query->get('title');
        return new Response($provider->getSvg($title), 200, ['content-type' => 'image/svg+xml']);
    }

    /**
     * Ajax for searching vertices by title
     */
    #[Route('/picture/vertex/search', methods: ['GET'])]
    public function vertexSearch(Request $request, VertexRepository $repository): JsonResponse
    {
        $title = $request->query->get('q', '');
        $choice = $repository->searchStartingWith($title);
        array_walk($choice, function (&$v, $k) {
            $v = ['pk' => (string) $v->_id, 'title' => $v->title];
        });

        return new JsonResponse($choice);
    }

    /**
     * Show the list of missing pictures
     */
    #[Route('/picture/broken', methods: ['GET'])]
    public function showBroken(VertexRepository $repository): Response
    {
        return $this->render('picture/broken.html.twig', ['broken' => $this->storage->searchForBrokenPicture($repository->search())]);
    }

}
