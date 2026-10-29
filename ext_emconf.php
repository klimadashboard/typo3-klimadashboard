<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Klimadashboard',
    'description' => 'Embed charts from Klimadashboard (klimadashboard.de / .at / .org) as content elements: chart picker, region, language, design skin, auto-height, DSGVO-friendly.',
    'category' => 'fe',
    'author' => 'Klimadashboard',
    'author_email' => 'team@klimadashboard.org',
    'author_company' => 'Klimadashboard – Verein zur Förderung datenbasierter Berichterstattung und Bewusstseinsbildung zur Klimakrise',
    'state' => 'beta',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.4.99',
            'typo3' => '11.5.0-14.4.99',
            'fluid_styled_content' => '11.5.0-14.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => ['Klimadashboard\\Typo3\\' => 'Classes/'],
    ],
];
