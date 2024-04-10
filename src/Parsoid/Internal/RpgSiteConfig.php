<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Internal;

use Wikimedia\Bcp47Code\Bcp47Code;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\Core\LinkTarget;
use Wikimedia\Parsoid\DOM\Document;

/**
 * Configuration of the internal wiki site for Parsoid
 */
class RpgSiteConfig extends SiteConfig
{

    protected array $interwikiMap = [
        'ep' => [
            'prefix' => 'ep',
            'url' => 'https://eclipse-savage.fandom.com/fr/wiki/$1'
        ]
    ];
    protected $namespaceMap = [
        '' => 0,
        'file' => 6,
        'template' => 7
    ];

    protected function getMagicWords(): array
    {
        return [];
    }

    protected function getNonNativeExtensionTags(): array
    {
        return [];
    }

    protected function getParameterizedAliasMatcher(array $words): callable
    {
        
    }

    protected function getProtocols(): array
    {
        return ["http:", "https:"];
    }

    protected function getSpecialNSAliases(): array
    {
        
    }

    protected function getSpecialPageAliases(string $specialPage): array
    {
        
    }

    protected function getVariableIDs(): array
    {
        return []; // None for now
    }

    protected function linkTrail(): string
    {
        return '';
    }

    public function allowedExternalImagePrefixes(): array
    {
        
    }

    public function baseURI(): string
    {
        return '/wiki'; // @todo use Router
    }

    public function bswRegexp(): string
    {
        
    }

    public function canonicalNamespaceId(string $name): ?int
    {
        return $this->namespaceMap[$name] ?? null;
    }

    public function categoryRegexp(): string
    {
        
    }

    public function exportMetadataToHeadBcp47(Document $document, ContentMetadataCollector $metadata, string $defaultTitle, Bcp47Code $lang): void
    {
        
    }

    public function getExternalLinkTarget()
    {
        
    }

    public function getMWConfigValue(string $key)
    {
        
    }

    public function getMagicWordMatcher(string $id): string
    {
        return '/(?!)/';
    }

    public function getMaxTemplateDepth(): int
    {
        return 40;
    }

    public function getNoFollowConfig(): array
    {
        
    }

    public function interwikiMagic(): bool
    {
        
    }

    public function interwikiMap(): array
    {
        return $this->interwikiMap;
    }

    public function iwp(): string
    {
        
    }

    public function langBcp47(): Bcp47Code
    {
        
    }

    public function langConverterEnabledBcp47(Bcp47Code $lang): bool
    {
        return false;
    }

    public function legalTitleChars(): string
    {
        return ' %!"$&\'()*,\-.\/0-9:;=?@A-Z\\\\^_`a-z~\x80-\xFF+';
    }

    public function linkPrefixRegex(): ?string
    {
        return null;
    }

    public function mainPageLinkTarget(): LinkTarget
    {
        return new RpgLinkTarget();
    }

    public function namespaceCase(int $ns): string
    {
        return 'case-sensitive';
    }

    public function namespaceHasSubpages(int $ns): bool
    {
        return false;
    }

    public function namespaceId(string $name): ?int
    {
        return null;
    }

    public function namespaceName(int $ns): ?string
    {
        return array_search($ns, $this->namespaceMap);
    }

    public function redirectRegexp(): string
    {
        
    }

    public function rtl(): bool
    {
        
    }

    public function script(): string
    {
        
    }

    public function scriptpath(): string
    {
        
    }

    public function server(): string
    {
        
    }

    public function specialPageLocalName(string $alias): ?string
    {
        
    }

    public function timezoneOffset(): int
    {
        
    }

    public function variantsFor(Bcp47Code $lang): ?array
    {
        
    }

    public function widthOption(): int
    {
        
    }

}
