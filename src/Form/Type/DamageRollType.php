<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\DamageRoll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DamageRollType extends AbstractType implements DataTransformerInterface
{

    public function getParent()
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
    }

    public function reverseTransform($value)
    {
        return DamageRoll::createFromString($value);
    }

    public function transform($value)
    {
       if (is_null($value)) {
            return '';
        }

        if (!$value instanceof DamageRoll) {
            throw new TransformationFailedException(json_encode($value) . ' is not a DamageRoll');
        }

        return (string) $value;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', DamageRoll::class);
    }

}
