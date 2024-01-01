<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\BoringAvatar;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Aggregation of multiple sources of avatar for NPC
 * Works better with AvatarType (but not mandatory)
 */
class AvatarMultisourceType extends AbstractType
{

    public function __construct(protected BoringAvatar $boringAvatar)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $seed = $options['random_prefix'];
        // for multiavatar
        $multiavatar = [$seed => $seed];
        for ($k = 1; $k < $options['max_sample']; $k++) {
            $val = $seed . rand();
            $multiavatar[$val] = $val;
        }

        // for abstract avatar
        $abstractAvatar = [];
        for ($k = 0; $k < $options['max_sample']; $k++) {
            $val = $seed . rand();
            $abstractAvatar[$val] = $this->boringAvatar->createBauhaus($val);
        }

        $builder
                ->add('internal', ChoiceType::class, [
                    'required' => false,
                    'choices' => $options['local_pictures'],
                    'block_prefix' => 'internal_avatar'
                ])
                ->add('invokeai', TextType::class, [
                    'required' => false,
                    'block_prefix' => 'invokeai_avatar',
                    'data' => $options['default_prompt'],
                    'attr' => ['x-model.fill' => 'query']
                ])
                ->add('multicultural', ChoiceType::class, [
                    'required' => false,
                    'choices' => $multiavatar,
                    'block_prefix' => 'multicultural_avatar'
                ])
                ->add('bauhaus', ChoiceType::class, [
                    'required' => false,
                    'choices' => $abstractAvatar,
                    'block_prefix' => 'bauhaus_avatar'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'max_sample' => 15,
            'default_prompt' => null,
            'mapped' => false
        ]);

        $resolver->setRequired(['random_prefix', 'local_pictures']);
    }

}
