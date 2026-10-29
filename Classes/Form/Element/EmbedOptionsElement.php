<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Form\Element;

use Klimadashboard\Typo3\Form\ItemsProcFunc;
use Klimadashboard\Typo3\Service\EmbedUrlBuilder;
use Klimadashboard\Typo3\Service\KlimadashboardApi;
use Klimadashboard\Typo3\Service\Settings;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renders one select per chart-specific embed option of the selected chart
 * (from GET /api/embed-options/<chartId>). The selects post an array, which
 * DataHandlerHook stores as a query string ("mode=bestand&historic=true").
 */
class EmbedOptionsElement extends AbstractFormElement
{
    private const LLL = 'LLL:EXT:klimadashboard/Resources/Private/Language/locallang_db.xlf:';

    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $parameterArray = $this->data['parameterArray'];
        $row = $this->data['databaseRow'];

        $chartId = ItemsProcFunc::getRowValue($row, 'tx_klimadashboard_chart');
        $site = ItemsProcFunc::getRowValue($row, 'tx_klimadashboard_site');
        if (!in_array($site, Settings::SITES, true)) {
            $site = GeneralUtility::makeInstance(Settings::class)->getDefaultSite();
        }
        $language = (string)($GLOBALS['BE_USER']->user['lang'] ?? '') === 'de' ? 'de' : 'en';

        $options = $chartId !== ''
            ? GeneralUtility::makeInstance(KlimadashboardApi::class)->getEmbedOptions($chartId, $site, $language)
            : [];
        $values = EmbedUrlBuilder::parseOptions((string)$parameterArray['itemFormElValue']);
        $name = (string)$parameterArray['itemFormElName'];
        // Always post the field, so removing all options clears the stored value
        $html = '<input type="hidden" name="' . htmlspecialchars($name) . '[_]" value="">';

        if ($options === []) {
            $message = $chartId === '' ? 'options.selectChart' : 'options.none';
            $html .= '<p class="text-body-secondary text-muted">' . htmlspecialchars($this->translate($message)) . '</p>';
        }

        foreach ($options as $option) {
            $id = 'kd-option-' . md5($name . $option['key']);
            $html .= '<div class="form-group mb-3">';
            $html .= '<label class="form-label t3js-formengine-label" for="' . $id . '">' . htmlspecialchars($option['label']) . '</label>';
            $html .= '<select class="form-select form-control form-control-adapt" id="' . $id . '" name="'
                . htmlspecialchars($name . '[' . $option['key'] . ']') . '">';
            $html .= '<option value="">' . htmlspecialchars(sprintf($this->translate('options.default'), $this->getChoiceLabel($option))) . '</option>';
            foreach ($option['choices'] as $choice) {
                $selected = ($values[$option['key']] ?? '') === $choice['value'] ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($choice['value']) . '"' . $selected . '>' . htmlspecialchars($choice['label']) . '</option>';
            }
            $html .= '</select></div>';
        }

        // Keep stored parameters the API does not (or no longer) offer, e.g. while it is unreachable
        $unlisted = array_diff_key($values, array_flip(array_column($options, 'key')));
        if ($unlisted !== []) {
            $html .= '<p class="text-body-secondary text-muted">' . htmlspecialchars($this->translate('options.stored')) . ' <code>'
                . htmlspecialchars(http_build_query($unlisted, '', '&', PHP_QUERY_RFC3986)) . '</code></p>';
            foreach ($unlisted as $key => $value) {
                $html .= '<input type="hidden" name="' . htmlspecialchars($name . '[' . $key . ']') . '" value="' . htmlspecialchars($value) . '">';
            }
        }

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $resultArray['html'] = '<div class="formengine-field-item t3js-formengine-field-item">'
            . $fieldInformationResult['html']
            . '<div class="form-wizards-wrap"><div class="form-wizards-element">' . $html . '</div></div>'
            . '</div>';
        return $resultArray;
    }

    /**
     * @param array{choices: array<int, array{value: string, label: string}>, default: string} $option
     */
    private function getChoiceLabel(array $option): string
    {
        foreach ($option['choices'] as $choice) {
            if ($choice['value'] === $option['default']) {
                return $choice['label'];
            }
        }
        return $option['default'];
    }

    private function translate(string $key): string
    {
        return (string)$this->getLanguageService()->sL(self::LLL . $key);
    }
}
