<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Form for uploading new picture
 */
class PictureUpload extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filename')
                ->add('picture', FileType::class, ['constraints' => [new Image()]])
                ->add('upload', SubmitType::class);
    }

}