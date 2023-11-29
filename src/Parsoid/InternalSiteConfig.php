<?php

/*
 * Bronze
 */

namespace App\Parsoid;

use BadMethodCallException;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\DOM\Document;
use Wikimedia\Parsoid\Utils\Utils;

/**
 * Configuration of the internal wiki site for Parsoid
 */
class InternalSiteConfig extends SiteConfig
{

    private $linkPrefixRegex = null;
    // @todo use the service
    protected array $interwikiMap = [
        'ep' => [
            'prefix' => 'ep',
            'url' => 'https://eclipse-savage.fandom.com/fr/wiki/$1'
        ]
    ];
    protected $linkTrailRegex = '/^([a-z]+)/sD'; // enwiki default
    protected $namespaceMap = [
        'media' => -2,
        'special' => -1,
        '' => 0,
        'file' => 6,
        'category' => 14,
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
        return fn($str) => null;
    }

    protected function getProtocols(): array
    {
        return ["http:", "https:"];
    }

    protected function getSpecialNSAliases(): array
    {
        return [];
    }

    protected function getSpecialPageAliases(string $specialPage): array
    {
        return [];
    }

    protected function getVariableIDs(): array
    {
        return []; // None for now
    }

    protected function linkTrail(): string
    {
        throw new BadMethodCallException('Should not be used. linkTrailRegex() is overridden here.');
    }

    public function allowedExternalImagePrefixes(): array
    {
        return [];
    }

    // @todo use the Router
    public function baseURI(): string
    {
        return '//127.0.0.1:8000/wiki';
    }

    public function bswRegexp(): string
    {
        return '/NOGLOBAL/';
    }

    public function canonicalNamespaceId(string $name): ?int
    {
        return $this->namespaceMap[$name] ?? null;
    }

    public function categoryRegexp(): string
    {
        return '/Category/';
    }

    public function exportMetadataToHead(Document $document, ContentMetadataCollector $metadata, string $defaultTitle, string $lang): void
    {
        $moduleLoadURI = $this->server() . $this->scriptpath() . '/load.php';

        // Look for a displaytitle.
        $displayTitle = $metadata->getPageProperty('displaytitle') ??
                // Use the default title, properly escaped
                Utils::escapeHtml($defaultTitle);

        $this->exportMetadataHelper(
                $document,
                $moduleLoadURI,
                $metadata->getModules(),
                $metadata->getModuleStyles(),
                $metadata->getJsConfigVars(),
                $displayTitle,
                $lang
        );
    }

    public function getExternalLinkTarget()
    {
        return '_blank';
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
        return [
            'nofollow' => true,
            'nsexceptions' => [1],
            'domainexceptions' => ['www.example.com']
        ];
    }

    public function interwikiMagic(): bool
    {
        return true;
    }

    public function interwikiMap(): array
    {
        return $this->interwikiMap;
    }

    public function iwp(): string
    {
        return 'mywiki';
    }

    public function lang(): string
    {
        return 'en';
    }

    public function langConverterEnabled(string $lang): bool
    {
        return false;
    }

    public function legalTitleChars(): string
    {
        return ' %!"$&\'()*,\-.\/0-9:;=?@A-Z\\\\^_`a-z~\x80-\xFF+';
    }

    public function linkPrefixRegex(): ?string
    {
        return $this->linkPrefixRegex;
    }

    public function mainpage(): string
    {
        // @todo
        return 'Main Page';
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
        $name = Utils::normalizeNamespaceName($name);
        return $this->namespaceMap[$name] ?? null;
    }

    public function namespaceName(int $ns): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = array_flip($this->namespaceMap);
        }
        if (!isset($map[$ns])) {
            return null;
        }
        return strtr($map[$ns], '_', ' ');
    }

    public function redirectRegexp(): string
    {
        return '/(?i:#REDIRECT)/';
    }

    public function responsiveReferences(): array
    {
        return [
            'enabled' => true,
            'threshold' => 10,
        ];
    }

    public function rtl(): bool
    {
        return false;
    }

    // @todo Wat ?
    public function script(): string
    {
        return '/wiki/index.php';
    }

    // @todo useless but should fallback or fail ?
    public function scriptpath(): string
    {
        return '/wiki';
    }

    // @todo redundant
    public function server(): string
    {
        return '//127.0.0.1:8000';
    }

    public function specialPageLocalName(string $alias): ?string
    {
        return null;
    }

    public function timezoneOffset(): int
    {
        return 0;
    }

    public function variants(): array
    {
        return [];
    }

    public function widthOption(): int
    {
        return 220;
    }

    // @todo useless since parsoid modules overrides - should be extracted from router
    public function relativeLinkPrefix(): string
    {
        return '/wiki/';
    }

    public function exportMetadataToHeadBcp47(Document $document, ContentMetadataCollector $metadata, string $defaultTitle, \Wikimedia\Bcp47Code\Bcp47Code $lang): void
    {
        
    }

    public function getMWConfigValue(string $key)
    {
        
    }

    public function langBcp47(): \Wikimedia\Bcp47Code\Bcp47Code
    {
        
    }

    public function langConverterEnabledBcp47(\Wikimedia\Bcp47Code\Bcp47Code $lang): bool
    {
        return false;
    }

    public function variantsFor(\Wikimedia\Bcp47Code\Bcp47Code $lang): ?array
    {
        return null;
    }

    public function ucfirst(string $str): string
    {
        return $str;
    }

}
