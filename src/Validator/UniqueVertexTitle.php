<?php

/*
 * eclipse-wiki
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueVertexTitle extends Constraint
{

    public $message = 'The title "{{ title }}" already exists.';

}
