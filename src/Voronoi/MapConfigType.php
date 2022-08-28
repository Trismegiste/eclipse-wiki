<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Form\FormTypeUtils;
use App\Form\Type\RandomIntegerType;
use App\Form\VertexType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * All parameters for generating a Voronoi map
 */
class MapConfigType extends AbstractType
{

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('seed', RandomIntegerType::class)
                ->add('side', IntegerType::class)
                ->add('avgTilePerRoom', IntegerType::class)
                ->add('erosionForHallway', CheckboxType::class, ['required' => false, 'property_path' => 'erosion'])
                ->add('erodingMinRoomSize', IntegerType::class)
                ->add('erodingMaxNeighbour', ChoiceType::class, [
                    'expanded' => true,
                    'choices' => [
                        6 => 6,
                        5 => 5,
                        4 => 4,
                        3 => 3
                    ]
                ])
                ->add('container', ChoiceType::class, [
                    'choices' => [
                        new Shape\NullShape(),
                        new Shape\Dome(),
                        new Shape\Border()
                    ],
                    'choice_label' => function (Shape\Strategy $strat): string {
                        return $strat->getName();
                    },
                    'choice_value' => function (?Shape\Strategy $strat): string {
                        return !is_null($strat) ? get_class($strat) : '';
                    }
                ])
                ->add('horizontalLines', IntegerType::class, ['required' => false, 'empty_data' => 0])
                ->add('doubleHorizontal', CheckboxType::class, ['required' => false])
                ->add('verticalLines', IntegerType::class, ['required' => false, 'empty_data' => 0])
                ->add('doubleVertical', CheckboxType::class, ['required' => false])
        ;

        $builder->get('content')->setRequired(false);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MapConfig::class,
            'empty_data' => function (FormInterface $form) {
                return new MapConfig($form->get('title')->getData());
            }
        ]);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->changeAttribute($view, 'content', 'rows', 1);
        $this->changeLabel($view, 'content', 'Informations');
        $this->moveChildAtEnd($view, 'content');
        $this->moveChildAtEnd($view, 'create');
    }

}
