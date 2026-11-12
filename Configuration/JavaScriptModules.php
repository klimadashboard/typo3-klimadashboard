<?php

// TYPO3 v12+ loads the backend preview script as an ES module through the import map.
// (TYPO3 v11 loads the same file through RequireJS as TYPO3/CMS/Klimadashboard/BackendPreview.)
return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@klimadashboard/klimadashboard/' => 'EXT:klimadashboard/Resources/Public/JavaScript/',
    ],
];
