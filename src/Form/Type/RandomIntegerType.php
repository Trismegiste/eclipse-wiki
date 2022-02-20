<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * An IntegerType with a random button
 */
class RandomIntegerType extends AbstractType
{

    public function getParent()
    {
        return IntegerType::class;
    }

}
