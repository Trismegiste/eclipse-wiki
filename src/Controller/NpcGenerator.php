<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Transhuman;
use App\Form\AliCreate;
use App\Form\FreeformCreate;
use App\Form\NpcAttacks;
use App\Form\NpcCreate;
use App\Form\NpcGears;
use App\Form\NpcInfo;
use App\Form\NpcResync;
use App\Form\NpcStats;
use App\Form\QuickNpc\SingleNodeChoice;
use App\Form\Type\ProviderChoiceType;
use App\Form\Type\WikiTitleType;
use App\Repository\BackgroundProvider;
use App\Repository\CharacterFactory;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use App\Repository\VertexRepository;
use App\Service\DocumentBroadcaster;
use App\Twig\SaWoExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use function mb_ucfirst;

/**
 * CRUD for NPC
 */
#[Route('/npc')]
class NpcGenerator extends AbstractController
{

    protected $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Creates a transhuman
     */
    #[Route('/create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $title = $request->query->get('title', '');

        $form = $this->createForm(NpcCreate::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/create.html.twig', [
                    'form' => $form->createView(),
                    'default_name' => mb_ucfirst($title)
        ]);
    }

    /**
     * Edits a NPC
     */
    #[Route('/edit/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function edit(Character $npc, Request $request): Response
    {
        $form = $this->createForm(NpcStats::class, $npc);
        $profile = $this->createForm(SingleNodeChoice::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render(SaWoExtension::editStatTemplate[get_class($npc)], ['profile' => $profile->createView(), 'form' => $form->createView()]);
    }

    /**
     * AJAX for getting background info
     */
    #[Route('/background/info', methods: ['GET'])]
    public function getBackground(Request $request, BackgroundProvider $provider): Response
    {
        $key = $request->query->get('key');
        $bg = $provider->findOne($key);

        return $this->render('fragment/background_detail.html.twig', ['background' => $bg]);
    }

    /**
     * AJAX for getting faction info
     */
    #[Route('/faction/info', methods: ['GET'], name: 'app_npcgenerator_getfaction')]
    public function getFaction(Request $request, FactionProvider $provider): Response
    {
        $key = $request->query->get('key');
        $fac = $provider->findOne($key);

        return $this->render('fragment/faction_detail.html.twig', ['faction' => $fac]);
    }

    /**
     * AJAX for getting morph info
     */
    #[Route('/morph/info', methods: ['GET'])]
    public function getMorph(Request $request, MorphProvider $provider): Response
    {
        $key = $request->query->get('key');
        $obj = $provider->findOne($key);

        return $this->render('fragment/morph_detail.html.twig', ['morph' => $obj]);
    }

    /**
     * Duplicate a NPC
     */
    #[Route('/duplicate/{pk}', methods: ['GET', 'POST'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function duplicate(Character $npc, Request $request): Response
    {
        $newNpc = clone $npc;
        $newNpc->setTitle($npc->getTitle() . ' (copie)');

        $form = $this->createFormBuilder($newNpc)
                ->add('title', WikiTitleType::class)
                ->add('wildCard', CheckboxType::class, ['required' => false])
                ->add('copy', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($newNpc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $newNpc->getPk()]);
        }

        return $this->render('form.html.twig', ['title' => 'Duplicate ' . $npc->getTitle(), 'form' => $form->createView()]);
    }

    /**
     * Form for editing gears & stuff of NPC
     */
    #[Route('/gear/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function gear(Character $npc, Request $request): Response
    {
        $form = $this->createForm(NpcGears::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);
            $this->addFlash('success', 'L\'équipement de ' . $npc->getTitle() . ' a été enregistré');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/gear.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Form for editing attacks and armors of NPC
     */
    #[Route('/battle/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function battle(Character $npc, Request $request): Response
    {
        $form = $this->createForm(NpcAttacks::class, $npc);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);
            $this->addFlash('success', 'Les paramètres de combat de ' . $npc->getTitle() . ' ont été enregistrés');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/battle.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates an A.L.I
     */
    #[Route('/ali', methods: ['GET', 'POST'])]
    public function ali(Request $request): Response
    {
        $form = $this->createForm(AliCreate::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('form.html.twig', ['title' => 'Création IAL', 'form' => $form->createView()]);
    }

    /**
     * Resleeves a NPC with a new morph
     */
    #[Route('/sleeve/{pk}', methods: ['GET', 'PATCH'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function sleeve(Character $npc, Request $request, MorphProvider $morph): Response
    {
        $form = $this->createFormBuilder($npc)
                ->add('morph', ProviderChoiceType::class, [
                    'provider' => $morph,
                    'placeholder' => '--- Choisissez un Morphe ---'
                ])
                ->add('sleeve', SubmitType::class)
                ->setMethod('PATCH')
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);
            $this->addFlash('success', 'La morphose de ' . $npc->getTitle() . ' a été enregistrée');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/sleeve.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates a freeform character
     */
    #[Route('/freeform', methods: ['GET', 'POST'])]
    public function freeform(Request $request): Response
    {
        $form = $this->createForm(FreeformCreate::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);

            return $this->redirectToRoute('app_npcgenerator_edit', ['pk' => $npc->getPk()]);
        }

        return $this->render('form.html.twig', ['title' => 'Création PNJ libre', 'form' => $form->createView()]);
    }

    /**
     * Edit information of NPC
     */
    #[Route('/info/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function info(Character $npc, Request $request): Response
    {
        $form = $this->createForm(NpcInfo::class, $npc, ['method' => 'PUT']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);
            $this->addFlash('success', 'Les informations de ' . $npc->getTitle() . ' ont été enregistrées');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/edit_info.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Creates an Extra NPC from a template and a new name
     */
    #[Route('/extra/{title}/{template}', methods: ['GET'], requirements: ['template' => '[\\da-f]{24}'])]
    public function createExtra(string $title, string $template, CharacterFactory $fac): Response
    {
        $npc = $this->repository->findByPk($template);
        if (!$npc instanceof Transhuman) {
            throw new NotFoundHttpException("$template is not a Transhuman");
        }
        $extra = $fac->createExtraFromTemplate($npc, $title);
        $this->repository->save($extra);
        $this->addFlash('success', "$title a été créé à partir de " . $npc->getTitle());

        return $this->redirectToRoute('app_profilepicture_token', ['pk' => $extra->getPk()]);
    }

    /**
     * Ajax get character in json
     */
    #[Route('/minicard', methods: ['GET'])]
    public function minicard(Request $request, Environment $twig): Response
    {
        /** @var Character $npc */
        $npc = $this->repository->findByTitle($request->get('title'));

        $dump = [
            'title' => $npc->getTitle(),
            'icon' => $twig->getFunction('vertex_icon')->getCallable()($npc),
            'sheet' => $this->generateUrl('app_vertexcrud_show', ['pk' => $npc->getPk()]),
            'instantiate' => null
        ];
        // link for instantiating new generic NPC
        if ($npc->getCategory() === 'transhuman' && $npc->isNpcTemplate()) {
            $dump['instantiate'] = $this->generateUrl('app_profilepicture_template', ['pk' => $npc->getPk()]);
        }

        foreach ($npc->extractPicture() as $pic) {
            $dump['picture'][] = [
                'link' => $this->generateUrl('app_picture_push', ['title' => $pic]),
                'thumb' => $this->generateUrl('get_picture', ['title' => $pic])
            ];
        }

        return $this->json($dump);
    }

    /**
     * Get NPC in json format
     */
    #[Route('/show.{_format}', methods: ['GET'], requirements: ['_format' => 'json'])]
    public function show(Request $request): Response
    {
        $npc = $this->repository->findByTitle($request->get('title'));
        if (is_null($npc)) {
            throw new NotFoundHttpException("NPC not found");
        }

        return new JsonResponse($npc);
    }

    /**
     * Resynchronize the NPC with its parent template
     */
    #[Route('/resync/{pk}', methods: ['GET', 'PUT'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function resync(Transhuman $npc, Request $request): Response
    {
        // check has a template parent
        if (is_null($npc->instantiatedFrom)) {
            throw $this->createNotFoundException($npc->getTitle() . ' has no parent template');
        }

        // check if the template parent exists
        $templateNpc = $this->repository->findByTitle($npc->instantiatedFrom);
        if (is_null($templateNpc)) {
            $this->addFlash('error', 'Le template NPC "' . $npc->instantiatedFrom . '" n\'existe pas.');

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        $form = $this->createForm(NpcResync::class, $npc, ['template' => $templateNpc]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $npc = $form->getData();
            $this->repository->save($npc);
            $this->addFlash('success', $npc->getTitle() . ' a été synchronisé avec ' . $npc->instantiatedFrom);

            return $this->redirectToRoute('app_vertexcrud_show', ['pk' => $npc->getPk()]);
        }

        return $this->render('npc/transhuman/resync.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Generates the Summary PDF and push to players
     */
    #[Route('/push-sheet/{pk}', methods: ['GET'], requirements: ['pk' => '[\\da-f]{24}'])]
    public function pushPdf(Character $vertex, DocumentBroadcaster $broadcast): Response
    {
        $title = sprintf("Stats-%s.pdf", $vertex->getTitle());
        $html = $this->renderView('npc/character_sheet.pdf.twig', ['vertex' => $vertex]);
        $pdf = $broadcast->generatePdf($title, $html);
        $this->addFlash('success', 'PDF Fiche de perso généré');
        
        return $this->redirectToRoute('app_gmpusher_pushdocument', [
                    'pk' => $vertex->getPk(),
                    'filename' => $pdf->getBasename(),
                    'label' => 'Fiche - ' . $vertex->getTitle()
        ]);
    }

}
