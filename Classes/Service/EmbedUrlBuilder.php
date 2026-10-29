<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Builds the Klimadashboard embed URL for a "Klimadashboard chart" content element.
 *
 * Contract (klimadashboard-core): /embed/<chartId>?auto=true&region=&lang=&skin=&showtext=&<chart options>
 */
class EmbedUrlBuilder implements SingletonInterface
{
    /** Parameters set by the extension itself; chart options may not override them */
    public const RESERVED_PARAMETERS = ['auto', 'region', 'lang', 'skin', 'showtext', 'bgcolor', 'notitle', 'view'];

    /** @var Settings */
    private $settings;

    public function __construct(?Settings $settings = null)
    {
        $this->settings = $settings ?? GeneralUtility::makeInstance(Settings::class);
    }

    /**
     * @param array<string, mixed> $row tt_content record
     * @param string $pageLanguage two-letter code of the current page language, used when the element follows the page
     */
    public function build(array $row, string $pageLanguage): string
    {
        $chartId = (string)($row['tx_klimadashboard_chart'] ?? '');
        if (!preg_match('/^[0-9a-zA-Z-]+$/', $chartId)) {
            return '';
        }

        $parameters = [];
        if ((int)($row['tx_klimadashboard_height'] ?? 0) === 0) {
            $parameters['auto'] = 'true';
        }
        $region = $this->getRegion($row);
        if ($region !== '') {
            $parameters['region'] = $region;
        }
        $parameters['lang'] = $this->getLanguage($row, $pageLanguage);
        $skin = (string)($row['tx_klimadashboard_skin'] ?? '');
        if ($skin !== '' && preg_match('/^[a-z0-9-]+$/', $skin)) {
            $parameters['skin'] = $skin;
        }
        if (!empty($row['tx_klimadashboard_hidetext'])) {
            $parameters['showtext'] = 'false';
        }
        foreach (self::parseOptions((string)($row['tx_klimadashboard_options'] ?? '')) as $key => $value) {
            if (!in_array($key, self::RESERVED_PARAMETERS, true)) {
                $parameters[$key] = $value;
            }
        }

        return $this->settings->getBaseUrl($this->getSite($row)) . '/embed/' . rawurlencode($chartId)
            . '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getSite(array $row): string
    {
        $site = (string)($row['tx_klimadashboard_site'] ?? '');
        return in_array($site, Settings::SITES, true) ? $site : $this->settings->getDefaultSite();
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getRegion(array $row): string
    {
        $region = trim((string)($row['tx_klimadashboard_region_custom'] ?? ''));
        if ($region === '') {
            $region = (string)($row['tx_klimadashboard_region'] ?? '');
        }
        return preg_match('/^[0-9a-zA-Z-]+$/', $region) ? $region : '';
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getLanguage(array $row, string $pageLanguage): string
    {
        $language = (string)($row['tx_klimadashboard_language'] ?? '');
        if ($language === '') {
            $language = $pageLanguage;
        }
        // Klimadashboard is available in German and English
        return $language === 'en' ? 'en' : 'de';
    }

    /**
     * Chart options are stored as a query string, e.g. "mode=bestand&historic=true".
     *
     * @return array<string, string>
     */
    public static function parseOptions(string $options): array
    {
        $parsed = [];
        parse_str($options, $parsed);
        $result = [];
        foreach ($parsed as $key => $value) {
            if (is_string($value) && $value !== '' && preg_match('/^[a-zA-Z0-9_-]+$/', (string)$key)) {
                $result[(string)$key] = $value;
            }
        }
        return $result;
    }
}
