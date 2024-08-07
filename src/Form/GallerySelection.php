<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Form\Type\PictureSelectionType;
use ArrayIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit entries in the session history pictures
 */
class GallerySelection extends AbstractType implements DataTransformerInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('gallery', CollectionType::class, [
                    'entry_type' => PictureSelectionType::class,
                    'block_prefix' => 'picture_collection'
                ])
                ->add('export', SubmitType::class)
                ->addModelTransformer($this)
        ;
    }

    public function reverseTransform(mixed $value): mixed
    {
        $listing = [];
        foreach ($value['gallery'] as $entry) {
            if ($entry['selected']) {
                $listing[] = $entry['picture'];
            }
        }

        return $listing;
    }

    public function transform(mixed $value): mixed
    {
        $mapped = [];
        foreach ($value as $entry) {
            $mapped[] = ['picture' => $entry, 'selected' => true];
        }
        return ['gallery' => new ArrayIterator($mapped)];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', null);
    }

}
