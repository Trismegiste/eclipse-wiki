<?php

namespace App\Service\StableDiffusion;

class PngReader
{

    private array $pngChunks = [];
    private \SplFileObject $filePtr;

    public function __construct(\SplFileInfo $png)
    {
        if (!$png->isFile()) {
            throw new \RuntimeException('File ' . $png->getBasename() . ' does not exist');
        }

        // Open the file
        try {
            $this->filePtr = $png->openFile('r');
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to open file ' . $png->getBasename(), $e->getCode(), $e);
        }

        // Read the magic bytes and verify
        $header = $this->filePtr->fread(8);

        if ($header != "\x89PNG\x0d\x0a\x1a\x0a") {
            throw new \RuntimeException($png->getBasename() . ' is not a valid PNG image');
        }

        // Loop through the chunks. Byte 0-3 is length, Byte 4-7 is type
        $chunkHeader = $this->filePtr->fread(8);

        while ($chunkHeader) {
            // Extract length and type from binary data
            $chunk = @unpack('Nsize/a4type', $chunkHeader);

            // Store position into internal array
            if (!key_exists($chunk['type'], $this->pngChunks)) {
                $this->pngChunks[$chunk['type']] = [];
            }
            $this->pngChunks[$chunk['type']][] = [
                'offset' => $this->filePtr->ftell(),
                'size' => $chunk['size']
            ];

            // Skip to next chunk (over body and CRC)
            $this->filePtr->fseek($chunk['size'] + 4, SEEK_CUR);

            // Read next chunk header
            $chunkHeader = $this->filePtr->fread(8);
        }
    }

    function __destruct()
    {
        unset($this->filePtr);
    }

    public function hasChunk(string $key): bool
    {
        return key_exists($key, $this->pngChunks);
    }

    public function getChunkTypes(): array
    {
        return array_keys($this->pngChunks);
    }

    /**
     * Returns all chunks of a given type
     * @param string $type
     * @return array
     * @throws \InvalidArgumentException 
     */
    public function getChunks(string $type): array
    {
        if (!$this->hasChunk($type)) {
            throw new \InvalidArgumentException("$type is not filled in");
        }

        $chunks = [];

        foreach ($this->pngChunks[$type] as $chunk) {
            if ($chunk['size'] > 0) {
                $this->filePtr->fseek($chunk['offset'], SEEK_SET);
                $chunks[] = $this->filePtr->fread($chunk['size']);
            } else {
                $chunks[] = '';
            }
        }

        return $chunks;
    }

    /**
     * Gets the "tExt" chunk
     * @return array
     */
    public function getTextChunk(): array
    {
        $rawTextData = $this->getChunks('tEXt');

        $metadata = [];
        foreach ($rawTextData as $data) {
            $sections = explode("\0", $data);

            if ($sections > 1) {
                $key = array_shift($sections);
                $metadata[$key] = implode("\0", $sections);
            } else {
                $metadata[] = $data;
            }
        }

        return $metadata;
    }

}
