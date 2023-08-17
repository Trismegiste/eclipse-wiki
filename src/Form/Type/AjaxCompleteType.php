<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\Strangelove\MongoDb\Repository;
use Trismegiste\Strangelove\MongoDb\Root;

/**
 * Description of AjaxCompleteType
 *
 * @author trismegiste
 */
class AjaxCompleteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new AjaxCompleteTransfo($options['repository']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['repository', 'ajax']);
        $resolver->setAllowedTypes('repository', Repository::class);
        $resolver->setDefault('data_class', Root::class);
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['ajax'] = $options['ajax'];
    }

}
