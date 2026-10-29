<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Preview;

use Klimadashboard\Typo3\Service\EmbedUrlBuilder;
use Klimadashboard\Typo3\Service\KlimadashboardApi;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Page module preview: chart title, region, skin and a link to the embed.
 */
class ChartPreviewRenderer extends StandardContentPreviewRenderer
{
    private const LLL = 'LLL:EXT:klimadashboard/Resources/Private/Language/locallang_db.xlf:';

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $row = $item->getRecord();
        // TYPO3 v14 passes a Record object here
        if (is_object($row) && method_exists($row, 'getRawRecord')) {
            $row = $row->getRawRecord()->toArray();
        } elseif (is_object($row) && method_exists($row, 'toArray')) {
            $row = $row->toArray();
        }
        $row = (array)$row;

        $api = GeneralUtility::makeInstance(KlimadashboardApi::class);
        $urlBuilder = GeneralUtility::makeInstance(EmbedUrlBuilder::class);
        $languageService = $this->getLanguageService();
        $language = (string)($GLOBALS['BE_USER']->user['lang'] ?? '') === 'de' ? 'de' : 'en';

        $chartId = (string)($row['tx_klimadashboard_chart'] ?? '');
        if ($chartId === '') {
            return '<p><em>' . htmlspecialchars($languageService->sL(self::LLL . 'preview.noChart')) . '</em></p>';
        }

        $lines = [];
        $region = $urlBuilder->getRegion($row);
        $regionName = $region !== '' ? ($api->getRegions()[$region]['name'] ?? $region) : '';
        $title = $api->getChartTitle($chartId, $language, $regionName) ?: $chartId;
        $lines[] = '<strong>' . htmlspecialchars($title) . '</strong>';

        if ($region !== '') {
            $lines[] = htmlspecialchars($languageService->sL(self::LLL . 'tt_content.tx_klimadashboard_region')) . ': ' . htmlspecialchars($regionName);
        }
        $skin = (string)($row['tx_klimadashboard_skin'] ?? '');
        if ($skin !== '') {
            $lines[] = htmlspecialchars($languageService->sL(self::LLL . 'tt_content.tx_klimadashboard_skin')) . ': '
                . htmlspecialchars($api->getSkins()[$skin] ?? $skin);
        }
        $url = $urlBuilder->build($row, $language);
        $lines[] = '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer">'
            . htmlspecialchars((string)parse_url($url, PHP_URL_HOST)) . ' ↗</a>';

        return '<p>' . implode('<br>', $lines) . '</p>';
    }
}
