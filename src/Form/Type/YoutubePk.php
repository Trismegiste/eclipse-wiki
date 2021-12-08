<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * a Youtube primary (the unique ID after the « youtube.com/watch?v= »
 */
class YoutubePk extends AbstractType implements DataTransformerInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function reverseTransform($value)
    {
        if (preg_match('#^https://(www\.)?youtube\.com/watch\?v=([-_a-zA-Z\d]{11})([\&]|$)#', $value, $extract, PREG_UNMATCHED_AS_NULL)) {
            return $extract[2];
        } else if (preg_match('#^https://youtu.be/([-_a-zA-Z\d]{11})$#', $value, $extract)) {
            return $extract[1];
        } else if (preg_match('#^([-_a-zA-Z\d]{11})$#', $value, $extract)) {
            return $extract[1];
        }

        return null;
    }

    public function transform($value)
    {
        return $value;
    }

}
