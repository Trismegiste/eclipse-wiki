<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\TileNpcConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * NPC config for each tile
 */
class TileNpcConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('npcTitle', NpcChoiceType::class, [
                    'placeholder' => '-----------',
                    'required' => false
                ])
                ->add('tilePerNpc', IntegerType::class, ['constraints' => [new Positive()], 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TileNpcConfig::class,
            'constraints' => [new Callback(function (TileNpcConfig $cfg, ExecutionContextInterface $ctx) {
                            // if there is a count but not a NPC
                            if (!empty($cfg->tilePerNpc)) {
                                if (empty($cfg->npcTitle)) {
                                    $ctx->buildViolation('You must select a NPC since you enter how many tiles per NPC')
                                            ->atPath('npcTitle')
                                            ->addViolation();
                                }
                            }
                            // if there is a NPC but no count
                            if (!empty($cfg->npcTitle)) {
                                if (empty($cfg->tilePerNpc)) {
                                    $ctx->buildViolation('You must set how many tiles per NPC since you select a NPC')
                                            ->atPath('tilePerNpc')
                                            ->addViolation();
                                }
                            }
                        })]
        ]);
    }

}
