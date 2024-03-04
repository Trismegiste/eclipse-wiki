<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use App\Form\Type\MorphInventory;
use App\Form\Type\ProviderChoiceType;
use App\Repository\MorphProvider;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Form for appending a morph bank inventory & price to the content of a Place
 */
class PlaceAppendMorphBank extends AbstractType implements DataMapperInterface
{

    public function __construct(protected VertexRepository $repository, protected MorphProvider $morph)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('morph_list', ProviderChoiceType::class, [
                    'provider' => $this->morph,
                    'placeholder' => '--- Ajouter un Morphe ---',
                    'attr' => [
                        'x-on:change' => 'morphSelect'
                    ]
                ])
                ->add('inventory', CollectionType::class, [
                    'entry_type' => MorphInventory::class,
                    'allow_add' => true,
                    'attr' => ['x-ref' => 'inventory'],
                    'block_prefix' => 'morph_inventory'
                ])
                ->add('append', SubmitType::class)
                ->setMethod('PUT')
        ;
        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
            'attr' => ['x-data' => 'morphBank']
        ]);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        
    }

}
