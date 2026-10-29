<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Read-only client for the public Klimadashboard APIs (Directus at base.klimadashboard.org and
 * the klimadashboard.* site APIs). Every response is cached; if an API is unreachable the last
 * good response is used, so the backend keeps working offline.
 */
class KlimadashboardApi implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const TIMEOUT = 8;

    /** @var RequestFactory */
    private $requestFactory;

    /** @var Settings */
    private $settings;

    /** @var FrontendInterface|null */
    private $cache;

    public function __construct(?RequestFactory $requestFactory = null, ?Settings $settings = null, ?CacheManager $cacheManager = null)
    {
        $this->requestFactory = $requestFactory ?? GeneralUtility::makeInstance(RequestFactory::class);
        $this->settings = $settings ?? GeneralUtility::makeInstance(Settings::class);
        try {
            $this->cache = ($cacheManager ?? GeneralUtility::makeInstance(CacheManager::class))->getCache('klimadashboard');
        } catch (\Throwable $e) {
            $this->cache = null;
        }
    }

    /**
     * Published charts.
     *
     * @return array<string, array{id: string, site: string, type: string, titles: array<string, string>}> id => chart
     */
    public function getCharts(): array
    {
        $url = $this->settings->getDirectusUrl() . '/items/charts?' . http_build_query([
            'filter' => ['status' => ['_eq' => 'published']],
            'fields' => 'id,site,type,custom_sveltestring,translations.languages_code,translations.title',
            'sort' => 'id',
            'limit' => -1,
        ]);
        $data = $this->fetchJson('charts', $url);
        $charts = [];
        foreach ((array)($data['data'] ?? []) as $row) {
            if (!is_array($row) || empty($row['id'])) {
                continue;
            }
            $type = (string)($row['custom_sveltestring'] ?? $row['type'] ?? '');
            if ($this->isExcluded($type)) {
                continue;
            }
            $titles = [];
            foreach ((array)($row['translations'] ?? []) as $translation) {
                if (is_array($translation) && !empty($translation['languages_code']) && !empty($translation['title'])) {
                    $titles[(string)$translation['languages_code']] = (string)$translation['title'];
                }
            }
            $charts[(string)$row['id']] = [
                'id' => (string)$row['id'],
                'site' => (string)($row['site'] ?? ''),
                'type' => $type,
                'titles' => $titles,
            ];
        }
        return $charts;
    }

    /**
     * Chart title with placeholders resolved: {{regionName}} becomes the region name (or "…"), others are removed.
     */
    public function getChartTitle(string $chartId, string $language, string $regionName = ''): string
    {
        $chart = $this->getCharts()[$chartId] ?? null;
        if ($chart === null) {
            return '';
        }
        $title = $chart['titles'][$language] ?? $chart['titles']['de'] ?? $chart['titles']['en'] ?? (reset($chart['titles']) ?: '');
        return self::resolveTitle($title, $regionName);
    }

    public static function resolveTitle(string $title, string $regionName = ''): string
    {
        $title = str_replace('{{regionName}}', $regionName !== '' ? $regionName : '…', $title);
        return trim((string)preg_replace(['/\{\{[^}]*\}\}/', '/\s+/'], ['', ' '], $title));
    }

    private function isExcluded(string $type): bool
    {
        foreach ($this->settings->getExcludedChartTypes() as $pattern) {
            if (fnmatch($pattern, $type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Regions for the configured layers and countries.
     *
     * @return array<string, array{id: string, name: string, layer: string, layerLabel: string, country: string}> id => region
     */
    public function getRegions(): array
    {
        $filter = ['layer' => ['_in' => implode(',', $this->settings->getRegionLayers())]];
        $countries = $this->settings->getRegionCountries();
        if ($countries !== []) {
            $filter['country'] = ['_in' => implode(',', $countries)];
        }
        $url = $this->settings->getDirectusUrl() . '/items/regions?' . http_build_query([
            'filter' => $filter,
            'fields' => 'id,name,layer,layer_label,country',
            'sort' => 'name',
            'limit' => -1,
        ]);
        $data = $this->fetchJson('regions', $url);
        $regions = [];
        foreach ((array)($data['data'] ?? []) as $row) {
            if (!is_array($row) || empty($row['id'])) {
                continue;
            }
            $regions[(string)$row['id']] = [
                'id' => (string)$row['id'],
                'name' => (string)($row['name'] ?? ''),
                'layer' => (string)($row['layer'] ?? ''),
                'layerLabel' => (string)($row['layer_label'] ?? ''),
                'country' => (string)($row['country'] ?? ''),
            ];
        }
        return $regions;
    }

    /**
     * Design skins: the configured list, merged with GET /api/skins from the default site.
     *
     * @return array<string, string> id => label
     */
    public function getSkins(): array
    {
        $skins = $this->settings->getConfiguredSkins();
        if ($this->settings->shouldFetchSkins()) {
            $url = $this->settings->getBaseUrl($this->settings->getDefaultSite()) . '/api/skins';
            foreach ((array)$this->fetchJson('skins', $url) as $skin) {
                $id = is_array($skin) ? strtolower((string)($skin['id'] ?? '')) : '';
                if ($id !== '' && preg_match('/^[a-z0-9-]+$/', $id)) {
                    $skins[$id] = (string)($skin['label'] ?? $id);
                }
            }
        }
        return $skins;
    }

    /**
     * Chart-specific embed options, from GET /api/embed-options/<chartId>.
     *
     * @return array<int, array{key: string, label: string, choices: array<int, array{value: string, label: string}>, default: string}>
     */
    public function getEmbedOptions(string $chartId, string $site, string $language): array
    {
        if (!preg_match('/^[0-9a-f-]{36}$/i', $chartId)) {
            return [];
        }
        $language = $language === 'en' ? 'en' : 'de';
        $url = $this->settings->getBaseUrl($site) . '/api/embed-options/' . $chartId . '?lang=' . $language;
        $options = [];
        foreach ((array)$this->fetchJson('options_' . $site . '_' . $chartId . '_' . $language, $url) as $option) {
            $key = is_array($option) ? (string)($option['key'] ?? '') : '';
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key) || in_array($key, EmbedUrlBuilder::RESERVED_PARAMETERS, true)) {
                continue;
            }
            $choices = [];
            foreach ((array)($option['choices'] ?? []) as $choice) {
                if (is_array($choice) && isset($choice['value'])) {
                    $choices[] = ['value' => (string)$choice['value'], 'label' => (string)($choice['label'] ?? $choice['value'])];
                }
            }
            $options[] = [
                'key' => $key,
                'label' => (string)($option['label'] ?? $key),
                'choices' => $choices,
                'default' => (string)($option['currentValue'] ?? ''),
            ];
        }
        return $options;
    }

    /**
     * @return mixed decoded JSON, or [] if neither the API nor a cached copy is available
     */
    private function fetchJson(string $identifier, string $url)
    {
        $cacheKey = 'api_' . sha1($identifier . '|' . $url);
        if ($this->cache !== null) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }

        try {
            $response = $this->requestFactory->request($url, 'GET', [
                'timeout' => self::TIMEOUT,
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => false,
            ]);
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string)$response->getBody(), true);
                if (is_array($data)) {
                    if ($this->cache !== null) {
                        $this->cache->set($cacheKey, $data, [], $this->settings->getCacheLifetime());
                        // Last good copy, used while the API is unreachable
                        $this->cache->set($cacheKey . '_stale', $data, [], 0);
                    }
                    return $data;
                }
            } elseif ($response->getStatusCode() === 404) {
                // e.g. a core version without this endpoint: cache the miss like a response
                if ($this->cache !== null) {
                    $this->cache->set($cacheKey, [], [], $this->settings->getCacheLifetime());
                }
                return [];
            }
            $this->logWarning('Unexpected response ' . $response->getStatusCode() . ' from ' . $url);
        } catch (\Throwable $e) {
            $this->logWarning('Request to ' . $url . ' failed: ' . $e->getMessage());
        }

        if ($this->cache !== null) {
            $stale = $this->cache->get($cacheKey . '_stale');
            if ($stale !== false) {
                return $stale;
            }
        }
        return [];
    }

    private function logWarning(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->warning('[klimadashboard] ' . $message);
        }
    }
}
