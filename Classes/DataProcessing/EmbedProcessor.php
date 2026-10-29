<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\DataProcessing;

use Klimadashboard\Typo3\Service\EmbedUrlBuilder;
use Klimadashboard\Typo3\Service\KlimadashboardApi;
use Klimadashboard\Typo3\Service\Settings;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Provides {klimadashboard} to the content element template: embed URL, iframe title,
 * height mode, consent placeholder settings and the allowed origins for the resize script.
 */
class EmbedProcessor implements DataProcessorInterface
{
    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $settings = GeneralUtility::makeInstance(Settings::class);
        $urlBuilder = GeneralUtility::makeInstance(EmbedUrlBuilder::class);
        $row = $processedData['data'];

        $language = $urlBuilder->getLanguage($row, $this->getPageLanguage($cObj));
        $site = $urlBuilder->getSite($row);
        $chartId = (string)($row['tx_klimadashboard_chart'] ?? '');
        $height = (int)($row['tx_klimadashboard_height'] ?? 0);
        $consent = (string)($row['tx_klimadashboard_consent'] ?? '');

        $api = GeneralUtility::makeInstance(KlimadashboardApi::class);
        $regionName = $api->getRegions()[$urlBuilder->getRegion($row)]['name'] ?? '';
        $title = $chartId !== '' ? $api->getChartTitle($chartId, $language, $regionName) : '';
        $baseUrl = $settings->getBaseUrl($site);

        $processedData[(string)($processorConfiguration['as'] ?? 'klimadashboard')] = [
            'url' => $urlBuilder->build($row, $this->getPageLanguage($cObj)),
            'title' => $title,
            'language' => $language,
            'autoHeight' => $height === 0,
            'height' => $height > 0 ? $height : 450,
            'consent' => $consent === '' ? $settings->isConsentEnabled() : $consent === 'on',
            'host' => (string)parse_url($baseUrl, PHP_URL_HOST),
            'privacyUrl' => $settings->getPrivacyUrl(),
            'origins' => implode(' ', $settings->getAllowedOrigins()),
        ];
        return $processedData;
    }

    private function getPageLanguage(ContentObjectRenderer $cObj): string
    {
        $request = method_exists($cObj, 'getRequest') ? $cObj->getRequest() : ($GLOBALS['TYPO3_REQUEST'] ?? null);
        $siteLanguage = $request !== null ? $request->getAttribute('language') : null;
        if ($siteLanguage === null) {
            return 'de';
        }
        if (method_exists($siteLanguage, 'getLocale') && is_object($siteLanguage->getLocale())) {
            return (string)$siteLanguage->getLocale()->getLanguageCode();
        }
        return method_exists($siteLanguage, 'getTwoLetterIsoCode') ? (string)$siteLanguage->getTwoLetterIsoCode() : 'de';
    }
}
