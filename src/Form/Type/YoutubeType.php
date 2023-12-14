<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * a Youtube primary key (the unique ID after the « youtube.com/watch?v= »
 */
class YoutubeType extends AbstractType implements DataTransformerInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this);
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function reverseTransform($value): mixed
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('#^https://(www\.)?youtube\.com/watch\?v=([-_a-zA-Z\d]{11})([\&]|$)#', $value, $extract, PREG_UNMATCHED_AS_NULL)) {
            return $extract[2];
        } else if (preg_match('#^https://youtu.be/([-_a-zA-Z\d]{11})$#', $value, $extract)) {
            return $extract[1];
        } else if (preg_match('#^([-_a-zA-Z\d]{11})$#', $value, $extract)) {
            return $extract[1];
        } else {
            throw new TransformationFailedException("Invalid format");
        }
    }

    public function transform($value): mixed
    {
        return $value;
    }

}
