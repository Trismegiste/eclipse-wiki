<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use LogicException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Param converter for Vertex collection
 */
class VertexConverter implements ParamConverterInterface
{

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $pk = $request->attributes->get('pk');
        if (empty($pk)) {
            throw new LogicException("Bad config : Attribute 'pk' is missing");
        }

        try {
            $entity = $this->repository->load($pk);
            $request->attributes->set($configuration->getName(), $entity);
            return true; // not sure if this is useful
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($configuration->getClass() . " '$pk' not found", $e);
        }
    }

    public function supports(ParamConverter $configuration): bool
    {
        return is_subclass_of($configuration->getClass(), Vertex::class, true);
    }

}
