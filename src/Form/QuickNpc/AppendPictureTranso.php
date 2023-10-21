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

        // update content
        $value->setContent("[[file:$importedName.jpg]]");
        $value->tokenPic = $tokenName;

        return $value;
    }

    public function transform(mixed $value): mixed
    {
        return null;
    }

}
