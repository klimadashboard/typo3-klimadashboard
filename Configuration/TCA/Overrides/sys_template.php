<?php

defined('TYPO3') or die();

// TYPO3 v11/v12: static TypoScript template. TYPO3 v13+: use the site set "klimadashboard/klimadashboard".
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'klimadashboard',
    'Configuration/TypoScript',
    'Klimadashboard'
);
