<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Form for uploading new picture
 */
class PictureUpload extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filename', TextType::class, [
                    'attr' => ['autocomplete' => 'off']
                ])
                ->add('picture', FileType::class, [
                    'constraints' => [new Image()],
                    'help' => 'COPY_PASTE_IMG'
                ])
                ->add('upload', SubmitType::class);
    }

}
