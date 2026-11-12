<?php

defined('TYPO3') or die();

(static function (): void {
    $lll = 'LLL:EXT:klimadashboard/Resources/Private/Language/locallang_db.xlf:';
    $tabs = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:';
    // "appearance" was added to the core tab labels in TYPO3 v13; fluid_styled_content's
    // own label exists in every supported version, so the tab keeps its name on v11/v12 too.
    $appearanceTab = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance';
    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();
    // Reads the extension configuration only; the field is left out of the form when the preview is off
    $previewEnabled = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \Klimadashboard\Typo3\Service\Settings::class
    )->isBackendPreviewEnabled();

    // TYPO3 v12+ uses associative select items, v11 numeric ones
    $item = static function (string $label, string $value, ?string $icon = null, ?string $group = null) use ($typo3Version): array {
        return $typo3Version >= 12
            ? ['label' => $label, 'value' => $value, 'icon' => $icon, 'group' => $group]
            : [$label, $value, $icon, $group];
    };

    $contentType = $item($lll . 'ctype.klimadashboard_chart', 'klimadashboard_chart', 'klimadashboard-chart', 'plugins');
    if ($typo3Version >= 13) {
        // Shown in the new content element wizard, which TYPO3 v13+ builds from TCA
        $contentType['description'] = $lll . 'ctype.klimadashboard_chart.description';
    }
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem('tt_content', 'CType', $contentType);
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['klimadashboard_chart'] = 'klimadashboard-chart';

    $columns = [
        'tx_klimadashboard_site' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_site',
            'description' => $lll . 'tt_content.tx_klimadashboard_site.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'site.default', ''),
                    $item('klimadashboard.de', 'de'),
                    $item('klimadashboard.at', 'at'),
                    $item('klimadashboard.org', 'org'),
                ],
                'default' => '',
            ],
        ],
        'tx_klimadashboard_chart' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_chart',
            'description' => $lll . 'tt_content.tx_klimadashboard_chart.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'chart.select', ''),
                ],
                'itemGroups' => [
                    'site' => $lll . 'chart.group.site',
                    'general' => $lll . 'chart.group.general',
                ],
                'itemsProcFunc' => \Klimadashboard\Typo3\Form\ItemsProcFunc::class . '->charts',
                'default' => '',
                // The items come from the API, so the list can be empty while it is unreachable.
                // Without this, TYPO3 v14 drops the field from the form instead of showing it empty.
                'showIfEmpty' => true,
            ],
        ],
        'tx_klimadashboard_language' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'language.page', ''),
                    $item($lll . 'language.de', 'de'),
                    $item($lll . 'language.en', 'en'),
                ],
                'default' => '',
            ],
        ],
        'tx_klimadashboard_region' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_region',
            'description' => $lll . 'tt_content.tx_klimadashboard_region.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'region.none', ''),
                ],
                'itemGroups' => [
                    'group' => $lll . 'region.group.group',
                    'state' => $lll . 'region.group.state',
                    'district' => $lll . 'region.group.district',
                    'other' => $lll . 'region.group.other',
                ],
                'itemsProcFunc' => \Klimadashboard\Typo3\Form\ItemsProcFunc::class . '->regions',
                'default' => '',
                'showIfEmpty' => true,
            ],
        ],
        'tx_klimadashboard_region_custom' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_region_custom',
            'description' => $lll . 'tt_content.tx_klimadashboard_region_custom.description',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 64,
                'eval' => 'trim',
                'placeholder' => '00000000-0000-0000-0000-000000000000',
            ],
        ],
        'tx_klimadashboard_skin' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_skin',
            'description' => $lll . 'tt_content.tx_klimadashboard_skin.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'skin.default', ''),
                ],
                'itemsProcFunc' => \Klimadashboard\Typo3\Form\ItemsProcFunc::class . '->skins',
                'default' => '',
                'showIfEmpty' => true,
            ],
        ],
        'tx_klimadashboard_options' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_options',
            'description' => $lll . 'tt_content.tx_klimadashboard_options.description',
            'config' => [
                'type' => 'text',
                'renderType' => 'klimadashboardEmbedOptions',
            ],
        ],
        // Not a database column: the element only renders the preview of the current form values
        'tx_klimadashboard_preview' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_preview',
            'config' => [
                'type' => 'user',
                'renderType' => 'klimadashboardChartPreview',
            ],
        ],
        'tx_klimadashboard_hidetext' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_hidetext',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'tx_klimadashboard_height' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_height',
            'description' => $lll . 'tt_content.tx_klimadashboard_height.description',
            'config' => $typo3Version >= 12
                ? ['type' => 'number', 'size' => 10, 'range' => ['lower' => 0, 'upper' => 3000], 'default' => 0]
                : ['type' => 'input', 'size' => 10, 'eval' => 'int', 'range' => ['lower' => 0, 'upper' => 3000], 'default' => 0],
        ],
        'tx_klimadashboard_consent' => [
            'label' => $lll . 'tt_content.tx_klimadashboard_consent',
            'description' => $lll . 'tt_content.tx_klimadashboard_consent.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    $item($lll . 'consent.inherit', ''),
                    $item($lll . 'consent.on', 'on'),
                    $item($lll . 'consent.off', 'off'),
                ],
                'default' => '',
            ],
        ],
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $columns);

    $GLOBALS['TCA']['tt_content']['palettes']['klimadashboard_source'] = [
        'label' => $lll . 'palette.source',
        'showitem' => 'tx_klimadashboard_site, tx_klimadashboard_language',
    ];
    $GLOBALS['TCA']['tt_content']['palettes']['klimadashboard_region'] = [
        'label' => $lll . 'palette.region',
        'showitem' => 'tx_klimadashboard_region, tx_klimadashboard_region_custom',
    ];
    $GLOBALS['TCA']['tt_content']['palettes']['klimadashboard_display'] = [
        'label' => $lll . 'palette.display',
        'showitem' => 'tx_klimadashboard_skin, tx_klimadashboard_hidetext, --linebreak--, tx_klimadashboard_height, tx_klimadashboard_consent',
    ];

    $GLOBALS['TCA']['tt_content']['types']['klimadashboard_chart'] = [
        'showitem' => '
            --div--;' . $tabs . 'general,
                --palette--;;general,
                --palette--;;headers,
            --div--;' . $lll . 'tab.chart,
                --palette--;;klimadashboard_source,
                tx_klimadashboard_chart,
                --palette--;;klimadashboard_region,
                tx_klimadashboard_options,
                --palette--;;klimadashboard_display,' . ($previewEnabled ? '
                tx_klimadashboard_preview,' : '') . '
            --div--;' . $appearanceTab . ',
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;' . $tabs . 'language,
                --palette--;;language,
            --div--;' . $tabs . 'access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;' . $tabs . 'categories,
                categories,
            --div--;' . $tabs . 'notes,
                rowDescription,
            --div--;' . $tabs . 'extended,
        ',
        'previewRenderer' => \Klimadashboard\Typo3\Preview\ChartPreviewRenderer::class,
    ];
})();
