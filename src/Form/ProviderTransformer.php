<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Description of ProviderTransformer
 */
class ProviderTransformer implements DataTransformerInterface
{

    protected $provider;

    public function __construct(\App\Repository\GenericProvider $pro)
    {
        $this->provider = $pro;
    }

    public function reverseTransform($value)
    {
        
    }

    public function transform($value)
    {
        
    }

}
