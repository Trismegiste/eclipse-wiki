<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use GdImage;
use function join_paths;

/**
 * History of Mercure push
 */
class SessionPushHistory extends LocalFileCache
{

    public function backupFile(GdImage $originFile, string $label): void
    {
        $targetFile = join_paths($this->folder, $label . '.jpg');
        imagejpeg($originFile, $targetFile, 85);
    }

}
