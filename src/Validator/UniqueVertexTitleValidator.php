<?php

/*
 * eclipse-wiki
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * The title of Vertex MUST be unique
 */
class UniqueVertexTitleValidator extends ConstraintValidator
{

    protected $repository;

    public function __construct(\App\Repository\VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueVertexTitle) {
            throw new UnexpectedTypeException($constraint, UniqueVertexTitle::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }

        $search = $this->repository->findByTitle($value);
        if (!is_null($search)) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ title }}', $value)
                    ->addViolation();
        }
    }

}
