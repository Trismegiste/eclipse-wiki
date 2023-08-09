<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use App\Service\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Traversable;

/**
 * Downloads, stores and append to the content of a given Vertex
 */
class AppendRemotePicture extends AbstractType implements DataMapperInterface
{

    public function __construct(protected Storage $storage)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper($this)
                ->add('local_name', TextType::class)
                ->add('picture_url', HiddenType::class, ['constraints' => [new NotBlank(), new Url()]])
                ->add('prompt_keywords', TextType::class, ['required' => false, 'mapped' => false])
                ->add('append', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Vertex::class);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $fields = iterator_to_array($forms);
        $urlField = $fields['picture_url'];
        /** @var Form $urlField */
        $url = $urlField->getData();
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $fields['prompt_keywords']->addError(new FormError('No Picture selected'));
            return;
        }

        $target = tmpfile();
        $pathname = stream_get_meta_data($target)['uri'];
        $success = @copy($url, $pathname);

        if (!$success) {
            $fields['prompt_keywords']->addError(new FormError('Unable to download the remote picture'));
            return;
        }

        $localName = $fields['local_name']->getData();
        try {
            $this->storage->storePicture(new UploadedFile($pathname, 'tmp.png'), $localName);
            $viewData->setContent($viewData->getContent() . "\n[[file:$localName.jpg]]");
        } catch (\RuntimeException $e) {
            $fields['local_name']->addError(new FormError($e->getMessage()));
        }
    }

}
