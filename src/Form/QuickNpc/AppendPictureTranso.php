<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\QuickNpc;

use App\Entity\Transhuman;
use App\Service\StableDiffusion\LocalRepository;
use App\Service\Storage;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Inject picture
 */
class AppendPictureTranso implements DataTransformerInterface
{

    public function __construct(protected LocalRepository $local, protected Storage $storage)
    {
        
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (!$value instanceof Transhuman) {
            throw new TransformationFailedException(get_class($value) . " is not Transhuman");
        }

        // upload picture
        $remoteName = $value->getContent();
        // if no picture, this transformer has no effect on the current Vertex
        if (is_null($remoteName)) {
            return $value;
        }

        $source = $this->local->getAbsoluteUrl($remoteName);
        $target = tmpfile();
        $pathname = stream_get_meta_data($target)['uri'];
        $success = @copy($source, $pathname);

        if (!$success) {
            throw new TransformationFailedException("Unable to download the remote picture");
        }

        // import into storage
        $importedName = $value->getTitle() . '-' . sha1($remoteName);
        $this->storage->storePicture(new UploadedFile($pathname, 'tmp.png'), $importedName);

        // managing avatar
        $full = imagecreatefrompng($pathname);
        $resized = imagescale($full, Storage::tokenSize, Storage::tokenSize);
        $tokenName = 'token-' . sha1($remoteName) . '.png';
        $tokenTarget = join_paths($this->storage->getRootDir(), $tokenName);
        imagepng($resized, $tokenTarget);

        // circle cropping
        $halfWidth = (int) (Storage::tokenSize / 2);
        $cropping = new \Symfony\Component\Process\Process([
            'convert',
            '-size', Storage::tokenSize . 'x' . Storage::tokenSize,
            'xc:none',
            '-fill', $tokenTarget,
            '-draw', "circle $halfWidth,$halfWidth $halfWidth,1",
            $tokenTarget
        ]);
        $cropping->mustRun();

        // update content
        $value->setContent("[[file:$importedName.jpg]]");
        $value->tokenPic = $tokenName;

        return $value;
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

}
