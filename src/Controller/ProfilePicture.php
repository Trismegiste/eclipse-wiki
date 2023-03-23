<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Transhuman;
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
    public function unique(Transhuman $npc, AvatarMaker $maker): Response
    {
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, $pathname);

        return new BinaryFileResponse($profile);
    }

    /**
     * Push a socnet profile for a unique Transhuman
     * @Route("/profile/unique/{pk}", methods={"POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function pushUnique(Transhuman $npc, AvatarMaker $maker, PlayerCastCache $cache): Response
    {
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $maker->generate($npc, $pathname);
        $cached = $cache->slimPictureForPush($profile);

        return $this->forward(PlayerCast::class . '::internalPushFile', ['pathname' => $cached->getPathname()]);
    }

    /**
     * Creates a battlemap token for a NPC
     * @Route("/npc/token/{pk}", methods={"GET","POST"}, requirements={"pk"="[\da-f]{24}"})
     */
    public function token(Character $npc, Request $request): Response
    {
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
    public function template(Transhuman $vertex, Request $request, AvatarMaker $maker, WebsocketPusher $pusher, PlayerCastCache $cache, CharacterFactory $fac): Response
    {
        $npc = clone $vertex;
        $form = $this->createForm(ProfileOnTheFly::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $form['avatar']->getData();
            // which button : push profile or create new NPC ?
            // Pushes the profile created on the fly
            if ($form->get('push_profile')->isClicked()) {
                $profile = $maker->generate($npc, $avatar);
                $cached = $cache->slimPictureForPush($profile);

                try {
                    $ret = $pusher->push(json_encode([
                        'file' => $cached->getPathname(),
                        'action' => 'pictureBroadcast'
                            ]), '2d');
                    $this->addFlash('success', $ret);
                } catch (ConnectionException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('app_profilepicture_template', ['pk' => $vertex->getPk()]);
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
