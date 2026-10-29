<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Typed access to the extension configuration (Admin Tools > Settings > Extension Configuration).
 */
class Settings implements SingletonInterface
{
    public const SITES = ['de', 'at', 'org'];

    /** @var array<string, mixed> */
    private $configuration;

    public function __construct(?ExtensionConfiguration $extensionConfiguration = null)
    {
        $extensionConfiguration = $extensionConfiguration ?? GeneralUtility::makeInstance(ExtensionConfiguration::class);
        try {
            $this->configuration = (array)$extensionConfiguration->get('klimadashboard');
        } catch (\Throwable $e) {
            $this->configuration = [];
        }
    }

    public function getBaseUrl(string $site): string
    {
        $defaults = ['de' => 'https://klimadashboard.de', 'at' => 'https://klimadashboard.at', 'org' => 'https://klimadashboard.org'];
        $site = in_array($site, self::SITES, true) ? $site : $this->getDefaultSite();
        $url = trim((string)($this->configuration['baseUrl' . ucfirst($site)] ?? ''));
        return rtrim($url !== '' ? $url : $defaults[$site], '/');
    }

    /**
     * Origins (scheme://host[:port]) the embeds may be loaded from; used for CSP and postMessage checks.
     *
     * @return string[]
     */
    public function getAllowedOrigins(): array
    {
        $origins = [];
        foreach (self::SITES as $site) {
            $parts = parse_url($this->getBaseUrl($site));
            if (!empty($parts['scheme']) && !empty($parts['host'])) {
                $origins[] = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            }
        }
        return array_values(array_unique($origins));
    }

    public function getDefaultSite(): string
    {
        $site = (string)($this->configuration['defaultSite'] ?? 'de');
        return in_array($site, self::SITES, true) ? $site : 'de';
    }

    public function getDirectusUrl(): string
    {
        $url = trim((string)($this->configuration['directusUrl'] ?? ''));
        return rtrim($url !== '' ? $url : 'https://base.klimadashboard.org', '/');
    }

    public function getCacheLifetime(): int
    {
        $lifetime = (int)($this->configuration['cacheLifetime'] ?? 14400);
        return $lifetime > 0 ? $lifetime : 14400;
    }

    /**
     * Chart types (custom_sveltestring) hidden from the chart select, wildcards allowed.
     *
     * @return string[]
     */
    public function getExcludedChartTypes(): array
    {
        return GeneralUtility::trimExplode(',', (string)($this->configuration['excludeChartTypes'] ?? 'test*'), true);
    }

    /** @return string[] */
    public function getRegionLayers(): array
    {
        return GeneralUtility::trimExplode(',', (string)($this->configuration['regionLayers'] ?? 'group,state,district'), true);
    }

    /** @return string[] */
    public function getRegionCountries(): array
    {
        return GeneralUtility::trimExplode(',', (string)($this->configuration['regionCountries'] ?? 'DE,AT'), true);
    }

    /**
     * Skins configured locally, format "id:Label, id2:Label 2" (comma or newline separated).
     *
     * @return array<string, string> id => label
     */
    public function getConfiguredSkins(): array
    {
        $skins = [];
        $raw = (string)($this->configuration['skins'] ?? '');
        foreach (preg_split('/[,\n]/', $raw) ?: [] as $entry) {
            $parts = explode(':', $entry, 2);
            $id = strtolower(trim($parts[0]));
            if ($id !== '' && preg_match('/^[a-z0-9-]+$/', $id)) {
                $skins[$id] = trim($parts[1] ?? '') ?: $id;
            }
        }
        return $skins;
    }

    public function shouldFetchSkins(): bool
    {
        return (bool)($this->configuration['fetchSkins'] ?? true);
    }

    public function isConsentEnabled(): bool
    {
        return (bool)($this->configuration['consent'] ?? false);
    }

    public function getPrivacyUrl(): string
    {
        return trim((string)($this->configuration['privacyUrl'] ?? ''));
    }
}
