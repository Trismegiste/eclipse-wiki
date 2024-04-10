<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Internal;

use Wikimedia\Bcp47Code\Bcp47Code;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Core\LinkTarget;
use Wikimedia\Parsoid\Utils\Utils;

/**
 * Description of RpgPageConfig
 *
 * @author trismegiste
 */
class RpgPageConfig extends PageConfig
{

    private LinkTarget $title;
    private $pagelanguage;

    public function __construct(protected PageContent $content)
    {
        $this->title = new RpgLinkTarget();
        $this->pagelanguage = Utils::mwCodeToBcp47('fr');
    }

    public function getContentModel(): string
    {
        return 'wikitext';
    }

    public function getLinkTarget(): LinkTarget
    {
        return $this->title;
    }

    public function getPageId(): int
    {
        return -1;
    }

    public function getPageLanguageBcp47(): Bcp47Code
    {
        return $this->pagelanguage;
    }

    public function getPageLanguageDir(): string
    {
        return 'ltr';
    }

    public function getParentRevisionId(): ?int
    {
        return null;
    }

    public function getRevisionContent(): ?PageContent
    {
        return $this->content;
    }

    public function getRevisionId(): ?int
    {
        return 1;
    }

    public function getRevisionSha1(): ?string
    {
        return null;
    }

    public function getRevisionSize(): ?int
    {
        
    }

    public function getRevisionTimestamp(): ?string
    {
        return null;
    }

    public function getRevisionUser(): ?string
    {
        
    }

    public function getRevisionUserId(): ?int
    {
        
    }

}
