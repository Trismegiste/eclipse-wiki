<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Transhuman;
use App\Form\ProfileOnTheFly;
use App\Form\ProfilePic;
use App\Repository\VertexRepository;
use App\Service\AvatarMaker;
use App\Service\Mercure\Pusher;
use App\Service\PlayerCastCache;
use App\Service\Storage;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Managing Social Networks Profile Pictures
 */
class ProfilePicture extends AbstractController
{

    protected $storage;
    protected $repository;

    public function __construct(Storage $store, VertexRepository $repo, protected AvatarMaker $maker)
    {
        $this->storage = $store;
        $this->repository = $repo;
    }

    /**
     * Generate a socnet profile for a unique Transhuman
     */
    #[Route('/profile/unique/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function unique(Transhuman $npc): StreamedResponse
    {
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $this->maker->generate($npc, $pathname);

        return new StreamedResponse(headers: ['Content-Type' => 'image/png'], callback: function () use ($profile): void {
                    imagepng($profile);
                });
    }

    /**
     * Push a socnet profile for a unique Transhuman
     */
    #[Route('/profile/unique/{pk}', methods: ['POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushUnique(Transhuman $npc, PlayerCastCache $cache): JsonResponse
    {
        $pathname = $this->storage->getFileInfo($npc->tokenPic);
        $profile = $this->maker->generate($npc, $pathname);

        return $this->forward(GmPusher::class . '::internalPushPicture', [
                    'label' => 'Profile ' . $npc->getTitle(),
                    'picture' => $profile,
                    'imgType' => 'profile'
        ]);
    }

    /**
     * Creates a battlemap token for a NPC
     */
    #[Route('/npc/token/{pk}', methods: ['GET', 'POST'], requirements: ['pk' => '[\\da-f]{24}'])]
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

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('picture/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Show a list of NPC profiles from a template (a Transhuman with isNpcTemplate() method returning true)
     */
    #[Route('/profile/template/{pk}', methods: ['GET', 'POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function template(Transhuman $vertex, Request $request, Pusher $pusher, PlayerCastCache $cache, \App\Service\SessionPushHistory $history): Response
    {
        $form = $this->createForm(ProfileOnTheFly::class, null, ['transhuman' => $vertex]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $extra = $form->getData();
            $avatar = $form['avatar']->getData();
            // which button : push profile or create new NPC ?
            //
            // Pushes the profile created on the fly
            if ($form->get('push_profile')->isClicked()) {
                $profile = $this->maker->generate($extra, $avatar);

                try {
                    $pusher->sendPictureAsDataUrl($profile, 'profile');
                    $history->backupFile($profile, 'Profile-' . $extra->getTitle());
                    $this->addFlash('success', 'Profile for ' . $extra->getTitle() . ' sent');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                return $this->redirectToRoute('app_profilepicture_template', ['pk' => $vertex->getPk()]);
            }

            // Creates a new NPC from the template
            if ($form->get('instantiate_npc')->isClicked()) {
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
