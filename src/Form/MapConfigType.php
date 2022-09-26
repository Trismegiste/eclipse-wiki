<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Entity\Shape\Strategy;
use App\Form\Type\RandomIntegerType;
use App\Service\ShapeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * All parameters for generating a Voronoi map
 */
class MapConfigType extends AbstractType
{

    protected ShapeProvider $provider;

    public function __construct(ShapeProvider $shapeProvider)
    {
        $this->provider = $shapeProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('side', IntegerType::class, ['constraints' => [new NotBlank(), new Positive()]])
                ->add('avgTilePerRoom', IntegerType::class, ['constraints' => [new NotBlank(), new Positive()]])
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
                    'choices' => $this->provider->findAll(),
                    'choice_label' => 'name',
                    'choice_value' => function (?Strategy $strat): string {
                        return !is_null($strat) ? get_class($strat) : '';
                    }
                ])
                ->add('horizontalLines', IntegerType::class, ['required' => false, 'empty_data' => 0])
                ->add('doubleHorizontal', CheckboxType::class, ['required' => false])
                ->add('verticalLines', IntegerType::class, ['required' => false, 'empty_data' => 0])
                ->add('doubleVertical', CheckboxType::class, ['required' => false])
                ->add('seed', RandomIntegerType::class, ['constraints' => [new NotBlank(), new Positive()]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MapConfig::class,
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

}
