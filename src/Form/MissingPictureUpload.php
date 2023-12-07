<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Form for uploading a new picture
 */
class MissingPictureUpload extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('picture', FileType::class, [
                    'constraints' => [new Image()],
                    'help' => 'COPY_PASTE_IMG',
                    'block_prefix' => 'pasted_file'
                ])
                ->add('upload', SubmitType::class)
        ;
    }

}
