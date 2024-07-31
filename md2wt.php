<?php

/*
 * Vesta
 */

$search = [
    '/^\d+(\.?)\s+\*\*([^\*]+)\*\*\s*\:\s*(.+)$/m',
    '/^\*\*([^\*]+)\*\*\s*$/m',
    '/\s\*\*([^\*]+)\*\*\s/m'
];

$replacement = [
    "===$2===\n$3",
    "===$1===",
    " '''$1''' "
];

$source = <<<MARKDOWN
coucou

2. **title1** : rien icidfdf fgdfg   
Paraph suppl
**autre titre**  
Content paraph with **hilite** youiyou

MARKDOWN;

echo preg_replace($search, $replacement, $source);
