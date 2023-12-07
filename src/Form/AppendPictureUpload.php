<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Form for uploading new picture and appends to the vertex content
 */
class AppendPictureUpload extends AbstractType
{

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('filename', TextType::class, [
                    'attr' => ['autocomplete' => 'off']
                ])
                ->add('picture', FileType::class, [
                    'constraints' => [new Image()],
                    'help' => 'COPY_PASTE_IMG',
                    'block_prefix' => 'pasted_file'
                ])
                ->add('append_vertex', Type\AjaxCompleteType::class, [
                    'required' => false,
                    'repository' => $this->repository,
                    'ajax' => $options['ajax_search']
                ])
                ->add('upload', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('ajax_search');
    }

}
