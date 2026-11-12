<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Form\Element;

use Klimadashboard\Typo3\Form\ItemsProcFunc;
use Klimadashboard\Typo3\Service\EmbedUrlBuilder;
use Klimadashboard\Typo3\Service\Settings;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Live preview of the selected chart inside the editing form.
 *
 * The iframe is rendered server-side from the stored values, and a small backend script
 * rebuilds its URL while the editor changes chart, region, options, skin, text or height,
 * so the preview follows the form without saving. The script only rewrites the URL of this
 * iframe; it posts nothing and stores nothing.
 */
class ChartPreviewElement extends AbstractFormElement
{
    private const LLL = 'LLL:EXT:klimadashboard/Resources/Private/Language/locallang_db.xlf:';

    /**
     * Height of the preview when the element adapts its height on the site. The preview never
     * asks for auto height: that makes the embed post its height to the window, and FormEngine
     * rejects messages from other origins with an uncaught error.
     */
    private const PREVIEW_HEIGHT = 500;

    /** Fields the preview follows; the backend script reads them by name */
    private const WATCHED_FIELDS = [
        'tx_klimadashboard_site',
        'tx_klimadashboard_chart',
        'tx_klimadashboard_language',
        'tx_klimadashboard_region',
        'tx_klimadashboard_region_custom',
        'tx_klimadashboard_skin',
        'tx_klimadashboard_hidetext',
        'tx_klimadashboard_height',
        'tx_klimadashboard_options',
    ];

    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $row = $this->data['databaseRow'];
        $settings = GeneralUtility::makeInstance(Settings::class);

        if (!$settings->isBackendPreviewEnabled()) {
            return $resultArray;
        }

        $urlBuilder = GeneralUtility::makeInstance(EmbedUrlBuilder::class);
        $language = $this->getPageLanguage();
        $chartId = ItemsProcFunc::getRowValue($row, 'tx_klimadashboard_chart');
        $height = (int)ItemsProcFunc::getRowValue($row, 'tx_klimadashboard_height');

        // The form posts selects as arrays; normalise them so the URL builder sees scalars
        $normalised = [];
        foreach (self::WATCHED_FIELDS as $field) {
            if ($field !== 'tx_klimadashboard_options') {
                $normalised[$field] = ItemsProcFunc::getRowValue($row, $field);
            }
        }
        $normalised['tx_klimadashboard_options'] = is_string($row['tx_klimadashboard_options'] ?? null)
            ? (string)$row['tx_klimadashboard_options']
            : '';
        // A fixed height keeps "auto" out of the URL, see PREVIEW_HEIGHT
        $previewHeight = $height > 0 ? $height : self::PREVIEW_HEIGHT;
        $normalised['tx_klimadashboard_height'] = (string)$previewHeight;

        $url = $chartId !== '' ? $urlBuilder->build($normalised, $language) : '';

        $sites = [];
        foreach (Settings::SITES as $site) {
            $sites[$site] = $settings->getBaseUrl($site);
        }

        $attributes = [
            'class' => 'klimadashboard-preview',
            'data-klimadashboard-preview' => 'true',
            'data-klimadashboard-prefix' => $this->getFieldPrefix(),
            'data-klimadashboard-sites' => (string)json_encode($sites),
            'data-klimadashboard-default-site' => $settings->getDefaultSite(),
            'data-klimadashboard-language' => $language,
            'data-klimadashboard-preview-height' => (string)self::PREVIEW_HEIGHT,
            'data-klimadashboard-reserved' => implode(',', EmbedUrlBuilder::RESERVED_PARAMETERS),
        ];
        $container = '';
        foreach ($attributes as $name => $value) {
            $container .= ' ' . htmlspecialchars($name) . '="' . htmlspecialchars($value) . '"';
        }

        $html = '<div' . $container . '>';
        $html .= '<div class="klimadashboard-preview__bar">';
        $html .= '<button type="button" class="btn btn-default btn-sm" data-klimadashboard-preview-reload>'
            . htmlspecialchars($this->translate('preview.reload')) . '</button>';
        $html .= '<a href="' . htmlspecialchars($url !== '' ? $url : '#') . '" target="_blank" rel="noopener noreferrer"'
            . ' class="klimadashboard-preview__open" data-klimadashboard-preview-open'
            . ($url === '' ? ' hidden' : '') . '>'
            . htmlspecialchars($this->translate('preview.open')) . '</a>';
        $html .= '</div>';
        $html .= '<p class="klimadashboard-preview__empty" data-klimadashboard-preview-empty'
            . ($url !== '' ? ' hidden' : '') . '>'
            . htmlspecialchars($this->translate('preview.selectChart')) . '</p>';
        $html .= '<iframe class="klimadashboard-preview__frame" data-klimadashboard-preview-frame'
            . ' title="' . htmlspecialchars($this->translate('preview.frameTitle')) . '"'
            . ' src="' . htmlspecialchars($url) . '"'
            . ' height="' . $previewHeight . '"'
            . ' referrerpolicy="strict-origin-when-cross-origin" loading="lazy"'
            . ($url === '' ? ' hidden' : '') . '></iframe>';
        $html .= '<p class="klimadashboard-preview__note">' . htmlspecialchars($this->translate('preview.note')) . '</p>';
        $html .= '</div>';

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $html = '<div class="formengine-field-item t3js-formengine-field-item">'
            . $fieldInformationResult['html']
            . '<div class="form-wizards-wrap"><div class="form-wizards-element">' . $html . '</div></div>'
            . '</div>';

        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();
        // TYPO3 v13+ elements render their own label; before, the surrounding container did
        $resultArray['html'] = $typo3Version >= 13 ? $this->wrapWithFieldsetAndLegend($html) : $html;
        $resultArray['stylesheetFiles'][] = 'EXT:klimadashboard/Resources/Public/Css/backend-preview.css';

        if ($typo3Version >= 12) {
            $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create(
                '@klimadashboard/klimadashboard/BackendPreview.js'
            );
        } else {
            // TYPO3 v11 loads backend JavaScript through RequireJS
            $resultArray['requireJsModules'][] = 'TYPO3/CMS/Klimadashboard/BackendPreview';
        }

        return $resultArray;
    }

    /**
     * The language the element falls back to when it is set to "Same as page": the language
     * of the site this record belongs to, so the preview matches what the frontend will render.
     */
    private function getPageLanguage(): string
    {
        $site = $this->data['site'] ?? null;
        $languageId = (int)ItemsProcFunc::getRowValue($this->data['databaseRow'] ?? [], 'sys_language_uid');
        if (is_object($site) && method_exists($site, 'getLanguageById')) {
            try {
                $siteLanguage = $site->getLanguageById($languageId);
                if (method_exists($siteLanguage, 'getLocale') && is_object($siteLanguage->getLocale())) {
                    return (string)$siteLanguage->getLocale()->getLanguageCode();
                }
                if (method_exists($siteLanguage, 'getTwoLetterIsoCode')) {
                    return (string)$siteLanguage->getTwoLetterIsoCode();
                }
            } catch (\Throwable $e) {
                // No site or no such language: fall back to the backend language below
            }
        }
        return (string)($GLOBALS['BE_USER']->user['lang'] ?? '') === 'de' ? 'de' : 'en';
    }

    /**
     * "data[tt_content][13]" — the backend script appends the field names to it.
     */
    private function getFieldPrefix(): string
    {
        $name = (string)($this->data['parameterArray']['itemFormElName'] ?? '');
        $bracket = strrpos($name, '[');
        return $bracket === false ? $name : substr($name, 0, $bracket);
    }

    private function translate(string $key): string
    {
        return (string)$this->getLanguageService()->sL(self::LLL . $key);
    }
}
