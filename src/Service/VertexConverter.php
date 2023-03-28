<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Param converter for Vertex collection
 */
class VertexConverter implements ValueResolverInterface
{

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // get the argument type (e.g. Timeline)
        $argumentType = $argument->getType();
        if (!$argumentType || !is_subclass_of($argumentType, Vertex::class, true)) {
            return [];
        }

        // check if there is a PK in parameters
        $pk = $request->attributes->get('pk');
        if (empty($pk)) {
            throw new LogicException("Bad config : Vertex can be only instantied with an attribute 'pk' in the route");
        }

        try {
            $entity = $this->repository->load($pk);
            return [$entity];
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException("$argumentType with the primary key '$pk' is not found", $e);
        }
    }

}
