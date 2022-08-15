<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use App\Form\Type\SurnameLanguageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Information about a Transhuman
 */
class NpcInfo extends AbstractType
{

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('surnameLang', SurnameLanguageType::class);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtEnd($view, 'create');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }
}
