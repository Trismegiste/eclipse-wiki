<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Submit button with waiting spinner
 */
class SubmitWaitType extends SubmitType
{

    public function getBlockPrefix(): string
    {
        return 'submit_wait';
    }

}
