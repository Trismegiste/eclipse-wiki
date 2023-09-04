<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use App\Service\Storage;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
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
                ->add('local_name', TextType::class, ['constraints' => [new NotBlank()]])
                ->add('append', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Vertex::class);
        $resolver->setRequired('picture_url');
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $fields = iterator_to_array($forms);
        /** @var Form $fields */
        $url = $fields['local_name']->getParent()->getConfig()->getOption('picture_url');
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $fields['local_name']->addError(new FormError('No Picture selected'));
            return;
        }

        $target = tmpfile();
        $pathname = stream_get_meta_data($target)['uri'];
        $success = @copy($url, $pathname);

        if (!$success) {
            $fields['local_name']->addError(new FormError('Unable to download the remote picture'));
            return;
        }

        $localName = $fields['local_name']->getData();
        try {
            $this->storage->storePicture(new UploadedFile($pathname, 'tmp.png'), $localName);
            $viewData->setContent($viewData->getContent() . "\n[[file:$localName.jpg]]");
        } catch (RuntimeException $e) {
            $fields['local_name']->addError(new FormError($e->getMessage()));
        }
    }

}
