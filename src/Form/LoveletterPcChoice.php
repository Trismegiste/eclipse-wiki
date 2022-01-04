<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Loveletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PC choice(s) for different resolutions from a love letter
 */
class LoveletterPcChoice extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Loveletter::class);
        $resolver->setDefined('edit');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('pc_choice', ChoiceType::class, [
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => range(0, 4), // dummy label replaced in the twig by resolution
                    'property_path' => 'pcChoice'
                ])
                ->add('select', SubmitType::class)
                ->setMethod('PUT');
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $resolution = $form->getData()->resolution;
        $choiceWidget = $view['pc_choice'];

        /*
          this part is tricky : we override the children in the widget (a.k.a the
          array of choices in the ChoiceType here) with a new label.
          This label comes from the resolution array from the object bound to the form.
          Note : the choices are only initialised with dummy labels in the form type ( @see self::buildForm )
         */
        foreach ($choiceWidget as $idx => $child) {
            if (!empty($resolution[$idx])) {
                $child->vars['label'] = $resolution[$idx];
            } else {
                unset($choiceWidget[$idx]);
            }
        }
    }

}
