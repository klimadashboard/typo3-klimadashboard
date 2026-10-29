<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Form;

use Klimadashboard\Typo3\Service\KlimadashboardApi;
use Klimadashboard\Typo3\Service\Settings;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Select items for the "Klimadashboard chart" content element, loaded from the Klimadashboard APIs.
 */
class ItemsProcFunc
{
    private const LLL = 'LLL:EXT:klimadashboard/Resources/Private/Language/locallang_db.xlf:';

    /** @var KlimadashboardApi */
    private $api;

    /** @var Settings */
    private $settings;

    public function __construct(?KlimadashboardApi $api = null, ?Settings $settings = null)
    {
        $this->api = $api ?? GeneralUtility::makeInstance(KlimadashboardApi::class);
        $this->settings = $settings ?? GeneralUtility::makeInstance(Settings::class);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function charts(array &$params): void
    {
        $site = self::getRowValue($params['row'] ?? [], 'tx_klimadashboard_site');
        if (!in_array($site, Settings::SITES, true)) {
            $site = $this->settings->getDefaultSite();
        }
        $language = $this->getBackendLanguage();

        $items = [];
        foreach ($this->api->getCharts() as $chart) {
            // Charts without a site are available everywhere, the others only on their site
            if ($chart['site'] !== '' && $chart['site'] !== $site) {
                continue;
            }
            $title = KlimadashboardApi::resolveTitle($chart['titles'][$language] ?? $chart['titles']['de'] ?? $chart['titles']['en'] ?? $chart['type']);
            $items[] = [
                'label' => $title,
                'value' => $chart['id'],
                'group' => $chart['site'] !== '' ? 'site' : 'general',
            ];
        }
        usort($items, static function (array $a, array $b): int {
            return strnatcasecmp($a['label'], $b['label']);
        });
        $this->addItems($params, $items, 'tx_klimadashboard_chart');
    }

    /**
     * @param array<string, mixed> $params
     */
    public function regions(array &$params): void
    {
        $items = [];
        foreach ($this->api->getRegions() as $region) {
            $details = array_filter([$region['layerLabel'], $region['country']]);
            $items[] = [
                'label' => $region['name'] . ($details !== [] ? ' (' . implode(', ', $details) . ')' : ''),
                'value' => $region['id'],
                'group' => in_array($region['layer'], ['group', 'state', 'district'], true) ? $region['layer'] : 'other',
            ];
        }
        $this->addItems($params, $items, 'tx_klimadashboard_region');
    }

    /**
     * @param array<string, mixed> $params
     */
    public function skins(array &$params): void
    {
        $items = [];
        foreach ($this->api->getSkins() as $id => $label) {
            $items[] = ['label' => $label, 'value' => $id, 'group' => ''];
        }
        $this->addItems($params, $items, 'tx_klimadashboard_skin');
    }

    /**
     * Appends items in the format of the running TYPO3 version and keeps a stored value that is
     * no longer offered by the API, so it is not lost when the record is saved.
     *
     * @param array<string, mixed> $params
     * @param array<int, array{label: string, value: string, group: string}> $items
     */
    private function addItems(array &$params, array $items, string $field): void
    {
        $current = self::getRowValue($params['row'] ?? [], $field);
        if ($current !== '' && !in_array($current, array_column($items, 'value'), true)) {
            $items[] = [
                'label' => sprintf($this->translate('item.unavailable'), $current),
                'value' => $current,
                'group' => '',
            ];
        }

        $associative = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 12;
        foreach ($items as $item) {
            if ($associative) {
                $params['items'][] = ['label' => $item['label'], 'value' => $item['value'], 'icon' => null, 'group' => $item['group'] ?: null];
            } else {
                $params['items'][] = [$item['label'], $item['value'], null, $item['group'] ?: null];
            }
        }
    }

    /**
     * FormEngine passes select values as arrays, DataHandler as strings.
     *
     * @param mixed $row
     */
    public static function getRowValue($row, string $field): string
    {
        $value = is_array($row) ? ($row[$field] ?? '') : '';
        if (is_array($value)) {
            $value = reset($value);
        }
        return (string)$value;
    }

    private function getBackendLanguage(): string
    {
        $user = $GLOBALS['BE_USER'] ?? null;
        $language = (string)($user->user['lang'] ?? $user->uc['lang'] ?? '');
        return $language === 'de' ? 'de' : 'en';
    }

    private function translate(string $key): string
    {
        $languageService = $GLOBALS['LANG'] ?? null;
        $label = $languageService instanceof LanguageService ? $languageService->sL(self::LLL . $key) : '';
        return $label !== '' ? $label : $key;
    }
}
