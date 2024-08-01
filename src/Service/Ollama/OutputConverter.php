<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Ollama;

/**
 * Converts output generated with LLM (markdown format) to app format
 */
class OutputConverter
{

    const search = [
        '/^\d+(\.?)\s+\*\*([^\*]+)\*\*\s*\:\s*(.+)$/m',
        '/^\d+(\.?)\s+\*\*([^\*]+)\*\*,\s(.+)$/m',
        '/^\d+\.\s([^\:]+)\s\:\s(.+)$/m',
        '/^\*\*([^\*]+)\*\*\s*$/m',
        '/\s\*\*([^\*]+)\*\*\s/m'
    ];
    const replacement = [
        "===$2===\n$3",
        "===$2===\n$3",
        "===$1===\n$2",
        "===$1===",
        " '''$1''' "
    ];

    public function toWikitext(string $source): string
    {
        return preg_replace(self::search, self::replacement, $source);
    }

}
