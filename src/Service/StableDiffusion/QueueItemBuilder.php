<?php

namespace App\Service\StableDiffusion;

class QueueItemBuilder
{

    public function generateWithDepth(string $assetPk): array
    {
        $query = file_get_content(__DIR__ . '/enqueue.json');

        return json_decode(str_replace('5617c3de-32c2-48d4-9c9f-f64b92e66831', $assetPk, $query), true);
    }

}
