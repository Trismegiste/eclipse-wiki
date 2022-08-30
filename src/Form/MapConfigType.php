<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Form\FormTypeUtils;
use App\Form\Type\RandomIntegerType;
use App\Form\VertexType;
use App\Voronoi\Shape\Border;
use App\Voronoi\Shape\Dome;
use App\Voronoi\Shape\NullShape;
use App\Voronoi\Shape\Strategy;
use App\Voronoi\Shape\Torus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
                ->add('erodingMinRoomSize', IntegerType::class, ['required' => false])
                ->add('erodingMaxNeighbour', ChoiceType::class, [
                    'required' => false,
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
                        new NullShape(),
                        new Border(),
                        new Dome(),
                        new Torus(),
                    ],
                    'choice_label' => 'name',
                    'choice_value' => function (?Strategy $strat): string {
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
            },
            'constraints' => [new Callback(function (MapConfig $cfg, ExecutionContextInterface $ctx) {
                            if ($cfg->erosion) {
                                if (empty($cfg->erodingMinRoomSize)) {
                                    $ctx->buildViolation('A minimum of room size for erosion must be set since erosion is enabled')
                                            ->atPath('erodingMinRoomSize')
                                            ->addViolation();
                                }

                                if (empty($cfg->erodingMaxNeighbour)) {
                                    $ctx->buildViolation('A maximum of neighbouring cells for erosion must be set since erosion is enabled')
                                            ->atPath('erodingMaxNeighbour')
                                            ->addViolation();
                                }
                            }
                        })]
        ]);
    }

    public function getParent(): ?string
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
