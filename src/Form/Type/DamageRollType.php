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

class DamageRollType extends AbstractType implements DataTransformerInterface
{

    public function getParent()
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
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

    public function reverseTransform($value): ?DamageRoll
    {
        if (!$value) {
            return null;
        }

        return DamageRoll::createFromString($value);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        // not sure why but it works 
        // Since it's a TextType, internal data is string typed. So far so good.
        // But strangley this option is not needed but in web ?!? Only in func test
        $resolver->setDefault('data_class', null); 
    }

}
