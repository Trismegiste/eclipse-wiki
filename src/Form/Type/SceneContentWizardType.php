<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Ali;
use App\Entity\Freeform;
use App\Entity\Place;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Twig\Environment;

/**
 * All question you need to ask when creation of a Scene
 */
class SceneContentWizardType extends AbstractType
{

    public function __construct(protected VertexRepository $repository, protected Environment $twig)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('place', AutocompleteType::class, [
                    'choices' => $this->getPlaceTitle(),
                    'required' => false,
                    'attr' => ['placeholder' => "Le titre d'un Décor existant"]
                ])
                ->add('ambience', WikitextType::class, [
                    'attr' => ['rows' => 4, 'placeholder' => "Une courte description de la mise en situation"],
                    'required' => false
                ])
                ->add('npc', CollectionType::class, [
                    'entry_type' => AutocompleteType::class,
                    'entry_options' => [
                        'choices' => $this->getNpcTitle(),
                        'required' => false,
                        'label' => false
                    ],
                    'delete_empty' => true,
                    'data' => array_fill(0, 4, null)
                ])
                ->add('prerequisite', WikitextType::class, ['required' => false, 'attr' => ['rows' => 4]])
                ->add('event', WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('outcome', WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('append_timeline', AutocompleteType::class, ['required' => false, 'choices' => $this->getTimelineTitle()])
        ;
        $builder->setDataMapper(new WikitextContentMapper($this->twig, 'scene/content.wiki.twig'));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', null);
        $resolver->setDefault('constraints',
                new Callback(function (string $value, ExecutionContextInterface $ctx) {
                            /** @var \Symfony\Component\Form\Form $form */
                            $form = $ctx->getObject();
                            $place = $form['place']->getData();
                            $ambience = $form['ambience']->getData();
                            if (empty($place) && empty($ambience)) {
                                $ctx->buildViolation('Either Place or Ambience should be filled')
                                        ->atPath('[place]')
                                        ->addViolation();
                            }
                        })
        );
    }

    protected function getPlaceTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass(Place::class) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

    protected function getTimelineTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass(Timeline::class) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

    protected function getNpcTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass([
            Ali::class,
            Transhuman::class,
            Freeform::class
        ]) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

}
