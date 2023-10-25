<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Repository\CreationGraphProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Select and apply one node of the creation graph
 */
class SingleNodeChoice extends AbstractType
{

    public function __construct(protected CreationGraphProvider $provider)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('node', ChoiceType::class, [
                    'required' => true,
                    'choices' => $this->provider->load(),
                    'choice_label' => function ($choice, string $key, mixed $value): string {
                        return $choice->name;
                    },
                    'choice_value' => function (?\App\Entity\CreationTree\Node $node): string {
                        return $node ? json_encode($node) : '{}';
                    },
                    'attr' => ['x-ref' => 'profile']
                ])
                ->add('apply', SubmitType::class)
        ;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', [
            'x-on:submit.prevent' => '$dispatch("profile", JSON.parse($refs.profile.value))'
        ]);
    }

}
