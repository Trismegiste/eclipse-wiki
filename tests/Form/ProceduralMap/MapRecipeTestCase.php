<?php

/*
 * Eclipse Wiki
 */

namespace App\Tests\Form\ProceduralMap;

use App\Form\Type\PlaceChoiceType;
use App\Repository\VertexRepository;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * Description of MapRecipeTestCase
 */
class MapRecipeTestCase extends TypeTestCase
{

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        // or if you also need to read constraints from annotations
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $type = new PlaceChoiceType($this->createStub(VertexRepository::class));

        return [
            new ValidatorExtension($validator),
            new PreloadedExtension([$type], []),
        ];
    }

}
