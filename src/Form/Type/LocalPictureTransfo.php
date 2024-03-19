<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Service\SessionPushHistory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Description of LocalPictureTransfo
 *
 * @author trismegiste
 */
class LocalPictureTransfo implements DataTransformerInterface
{

    public function __construct(protected SessionPushHistory $history)
    {
        
    }

    public function reverseTransform(mixed $value): mixed
    {
        try {
            return $this->history->getFileInfo($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException(previous: $e);
        }
    }

    public function transform(mixed $value): mixed
    {
        if (!$value instanceof \SplFileInfo) {
            throw new TransformationFailedException('Bad typing');
        }
        return $value->getFilename();
    }

}
