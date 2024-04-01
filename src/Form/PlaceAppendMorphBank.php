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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Form for appending a morph bank inventory & price to the content of a Place
 */
class PlaceAppendMorphBank extends AbstractType implements DataMapperInterface
{

    const defaultScarcity = [
        1 => ['bonus' => 1],
        2 => ['bonus' => 2],
        3 => ['bonus' => 3],
        4 => ['bonus' => 4],
        '1d2' => [2 => 1],
        '1d3' => [3 => 1],
        '1d4' => [4 => 1],
        '1d3+1' => [3 => 1, 'bonus' => 1],
    ];
    const defaultStock = [
        5 => ['bonus' => 5],
        10 => ['bonus' => 10],
        '1d4' => [4 => 1],
        '1d8' => [8 => 1],
        '2d6' => [6 => 2],
        '2d10' => [10 => 2],
        '3d10' => [10 => 3],
        '5d10' => [10 => 5],
    ];

    public function __construct(protected VertexRepository $repository, protected MorphProvider $morph)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('title', TextType::class, [
                    'required' => false,
                    'empty_data' => 'Banque de morphes',
                    'attr' => ['placeholder' => 'Nom de la banque de morphes (par défaut : "Banque de morphes" si laissé vide)']
                ])
                ->add('morph_list', ProviderChoiceType::class, [
                    'provider' => $this->morph,
                    'attr' => [
                        'x-on:change' => 'morphSelect',
                        'size' => 25,
                        'multiple' => true
                    ],
                    'required' => false,
                    'placeholder' => false
                ])
                ->add('scarcity', ChoiceType::class, [
                    'choices' => array_map(fn($v) => json_encode($v), self::defaultScarcity),
                    'attr' => ['x-model' => 'scarcity']
                ])
                ->add('stock', ChoiceType::class, [
                    'choices' => array_map(fn($v) => json_encode($v), self::defaultStock),
                    'attr' => ['x-model' => 'stock']
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
        /** @var Place $viewData */
        $fields = iterator_to_array($forms);
        $title = $fields['title']->getData();
        $table = "{|\n|+ {{morphbank|$title}}\n!Morphe!!Dispo!!Stock\n";
        foreach ($fields['inventory']->getData() as $entry) {
            $table .= "|-\n";
            $table .= "|{$entry['morph']}||{$entry['scarcity']}||{$entry['stock']}\n";
        }
        $table .= "|}";

        $viewData->setContent($viewData->getContent() . "\n\n$table\n");
    }

}
