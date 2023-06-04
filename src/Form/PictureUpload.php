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
use Symfony\Component\Validator\Constraints\Image;

/**
 * Form for uploading new picture
 */
class PictureUpload extends AbstractType
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
                    'help' => 'COPY_PASTE_IMG'
                ])
                ->add('append_vertex', Type\AutocompleteType::class, ['required' => false, 'choices' => $this->getVertexTitle()])
                ->add('upload', SubmitType::class);
    }

    protected function getVertexTitle(): array
    {
        // we need a flat array and not a generator since this widget could be embedded in collection
        $choice = [];
        foreach ($this->repository->findAll() as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

}
