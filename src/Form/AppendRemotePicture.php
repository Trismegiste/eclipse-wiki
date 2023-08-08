<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use App\Service\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Downloads, stores and append to the content of a given Vertex
 */
class AppendRemotePicture extends AbstractType
{

    public function __construct(protected Storage $storage)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('content', TextType::class, [
                    'label' => "Prompt fragments",
                    'setter' => function (Vertex &$v, string $data) {
                        $target = tmpfile();
                        $pathname = stream_get_meta_data($target)['uri'];
                        copy($data, $pathname);
                        $this->storage->storePicture(new UploadedFile($pathname, 'tmp.png'), 'tmp');
                        $v->setContent($v->getContent() . " [[file:tmp.jpg]]");
                    },
                    'data' => null,
                    'constraints' => [new Url()]
                ])
                ->add('append', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Vertex::class);
    }

}
