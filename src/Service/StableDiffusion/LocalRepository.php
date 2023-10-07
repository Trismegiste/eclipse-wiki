<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function join_paths;

/**
 * Local repository of InvokeAI pictures
 */
class LocalRepository extends PictureRepository
{

    protected string $root;

    public function __construct(string $projectDir, protected UrlGeneratorInterface $routing)
    {
        $this->root = join_paths($projectDir, 'var/invokeai');
    }

    /**
     * The root directory where files are stored
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->root;
    }

    public function getAbsoluteUrl(string $name): string
    {
        return 'file://' . join_paths($this->root, $name . '.png');
    }

    public function getThumbnailUrl(string $name): string
    {
        return $this->routing->generate('app_invokeaipicture_getlocal', ['pic' => $name], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function searchPicture(string $query, int $capFound = 10): array
    {
        $iter = new Finder();
        $iter->in($this->root)
                ->files()
                ->name("*.png")
                ->getIterator();

        $keywords = $this->splittingPrompt($query);
        $found = [];

        foreach ($iter as $picture) {
            /** @var SplFileInfo $picture */
            $reader = new InvokeAiReader($picture);
            if (!$this->matchKeywordAndPrompt($keywords, $reader->getPositivePrompt())) {
                continue;
            }
            $width = $reader->getWidth();
            unset($reader);

            $keyImg = $picture->getBasename('.png');
            $found[] = new PictureInfo(
                    $this->getAbsoluteUrl($keyImg),
                    $this->getThumbnailUrl($keyImg),
                    $width,
                    $keyImg
            );
        }

        return $found;
    }

    public function getPictureResponse(string $name): BinaryFileResponse
    {
        return new BinaryFileResponse(join_paths($this->root, $name . '.png'));
    }

}
