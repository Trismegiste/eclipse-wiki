<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Form\ProfileOnTheFly;
use App\Form\ProfilePic;
use App\Repository\CharacterFactory;
use App\Repository\VertexRepository;
use App\Service\AvatarMaker;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use App\Service\WebsocketPusher;
use Paragi\PhpWebsocket\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Managing Social Networks Profile Pictures
 */
class ProfilePicture extends AbstractController
{

    protected $storage;
    protected $repository;

    public function __construct(Storage $store, VertexRepository $repo)
    {
        $this->storage = $store;
        $this->repository = $repo;
    }

    /**
     * Generate a socnet profile for a unique Transhuman
     * @Route("/profile/unique/{pk}", methods={"GET"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function unique(string $pk, AvatarMaker $maker): Response
    {
        $npc = $this->repository->findByPk($pk);
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, $pathname);

        return new BinaryFileResponse($profile);
    }

    /**
     * Push a socnet profile for a unique Transhuman
     * @Route("/profile/unique/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushUnique(string $pk, AvatarMaker $maker, PlayerCastCache $cache): Response
    {
        $npc = $this->repository->findByPk($pk);
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, $pathname);
        $cached = $cache->slimPictureForPush($profile);

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $cached->getPathname()]);
    }

    /**
     * Creates a battlemap token for a NPC
     * @Route("/npc/token/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function token(string $pk, Request $request): Response
    {
        $npc = $this->repository->findByPk($pk);
        $form = $this->createForm(ProfilePic::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = 'token-' . $npc->getPk() . '.png';
            $this->storage->storeToken($form['avatar']->getData(), $filename);
            $npc->tokenPic = $filename;
            $this->repository->save($npc);
            $this->addFlash('success', 'Token généré');

            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show a list of NPC profiles from a template (a Transhuman with isNpcTemplate() method returns true)
     * @Route("/profile/template/{pk}", methods={"GET", "POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function template(string $pk, Request $request, AvatarMaker $maker, WebsocketPusher $pusher, PlayerCastCache $cache, CharacterFactory $fac): Response
    {
        $vertex = $this->repository->findByPk($pk);
        $npc = clone $vertex;
        $form = $this->createForm(ProfileOnTheFly::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // which button : push or create ?
            $avatar = $form['avatar']->getData();

            // Pushes the profile created on the fly
            if ($form->get('push_profile')->isClicked()) {
                $profile = $maker->generate($npc, $avatar);
                $cached = $cache->slimPictureForPush($profile);

                try {
                    $ret = $pusher->push(json_encode([
                        'file' => $cached->getPathname(),
                        'action' => 'pictureBroadcast'
                    ]));
                    $this->addFlash('success', $ret);
                } catch (ConnectionException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('app_profilepicture_template', ['pk' => $pk]);
            }

            // Instantiate the NPC from the template
            if ($form->get('instantiate_npc')->isClicked()) {
                $extra = $fac->createExtraFromTemplate($vertex, $npc->getTitle());
                $this->repository->save($extra);

                $filename = 'token-' . $extra->getPk() . '.png';
                $this->storage->storeToken($avatar, $filename);
                $extra->tokenPic = $filename;
                $this->repository->save($extra);

                $this->addFlash('success', $extra->getTitle() . " a été créé à partir de " . $vertex->getTitle());
                return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $extra->getPk()]);
            }
        }

        return $this->render('picture/profile_instantiate.html.twig', [
                    'form' => $form->createView(),
                    'npc' => $vertex
        ]);
    }

}
