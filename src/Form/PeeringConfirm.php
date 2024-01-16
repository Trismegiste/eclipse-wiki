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
                ->add('key', IntegerType::class, ['attr' => ['x-model' => 'selectedKey']])
                ->add('npc', ChoiceType::class, [
                    'choices' => $this->vertexRepo->findByClass(Transhuman::class, ['wildCard' => true]),
                    'choice_label' => function ($choice, string $key, mixed $value): string {
                        return $choice->getTitle();
                    }
                ])
                ->add('confirm', SubmitType::class)
        ;
    }

}
