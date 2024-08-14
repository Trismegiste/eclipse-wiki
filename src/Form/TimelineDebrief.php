<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Timeline;
use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit the debrief in a timeline
 */
class TimelineDebrief extends AbstractType
{

    public function getParent(): string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder->add('debriefing', WikitextType::class, ['required' => false, 'attr' => ['rows' => 25]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Timeline::class);
    }

}
