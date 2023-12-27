<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\QuickNpc;

use App\Entity\Transhuman;
use App\Service\StableDiffusion\InvokeAiReader;
use App\Service\StableDiffusion\PictureRepository;
use App\Service\Storage;
use SplFileInfo;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;
use function join_paths;

/**
 * Inject picture
 */
class AppendPictureTranso implements DataTransformerInterface
{

    public function __construct(protected PictureRepository $local, protected Storage $storage, protected bool $deleteAfterUsing = false)
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
        $cropping = new Process([
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
        $value->tokenPicPrompt = $this->getPrompt($source);

        if (file_exists($tokenTarget) && $this->deleteAfterUsing) {
            unlink($source);
        }

        return $value;
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

    // getting positive prompt from original picture
    protected function getPrompt(string $pathname): string
    {
        $reader = new InvokeAiReader(new SplFileInfo($pathname));
        return $reader->getPositivePrompt();
    }

}
