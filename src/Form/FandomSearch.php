<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Enum\SearchNamespace;
use App\Service\MediaWiki;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Remote search on fandom Mediawiki through API
 */
class FandomSearch extends AbstractType implements DataTransformerInterface
{

    public function __construct(protected MediaWiki $wiki)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('query')
                ->add('namespace', EnumType::class, [
                    'class' => SearchNamespace::class,
                    'multiple' => false,
                    'expanded' => true
                ])
                ->add('search', SubmitType::class)
                ->setMethod('GET')
                ->addModelTransformer($this)
        ;
    }

    public function reverseTransform(mixed $value): mixed
    {
        return [
            'namespace' => $value['namespace'],
            'listing' => match ($value['namespace']) {
                SearchNamespace::Pages => $this->wiki->searchPageByName($value['query']),
                SearchNamespace::Images => $this->wiki->extractUrlFromGallery($this->wiki->renderGallery($this->wiki->searchImage($value['query'])))
            }
        ];
    }

    public function transform(mixed $value): mixed
    {
        return null;
    }

}
