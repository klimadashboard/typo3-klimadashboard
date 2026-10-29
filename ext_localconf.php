<?php

defined('TYPO3') or die();

(static function (): void {
    // Cache for API responses (charts, regions, skins, embed options)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['klimadashboard'] ??= [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'groups' => ['system'],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1730198400] = [
        'nodeName' => 'klimadashboardEmbedOptions',
        'priority' => 40,
        'class' => \Klimadashboard\Typo3\Form\Element\EmbedOptionsElement::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['klimadashboard']
        = \Klimadashboard\Typo3\Hooks\DataHandlerHook::class;

    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();
    if ($typo3Version < 13) {
        // TYPO3 v13+ builds the new content element wizard from TCA
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            "@import 'EXT:klimadashboard/Configuration/TsConfig/Page/ContentElementWizard.tsconfig'"
        );
    }
})();
