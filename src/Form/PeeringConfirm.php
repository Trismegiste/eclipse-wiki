<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Confirm a peering with player
 */
class PeeringConfirm extends AbstractType
{

    public function __construct(protected VertexRepository $vertexRepo)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('key', IntegerType::class, [
                    'constraints' => [new NotBlank()]
                ])
                ->add('pc', ChoiceType::class, [
                    'choices' => $this->vertexRepo->findByClass(Transhuman::class, ['wildCard' => true]),
                    'choice_label' => function ($choice, string $key, mixed $value): string {
                        return $choice->getTitle();
                    },
                    'choice_value' => function (?Transhuman $choice): string {
                        return $choice ? $choice->getPk() : '';
                    },
                    'placeholder' => '------------',
                    'required' => true
                ])
                ->add('confirm', SubmitType::class, ['attr' => ['x-bind:disabled' => 'player.length === 0']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', ['x-on:submit.prevent' => 'validation']);
    }

}
