<?php

declare(strict_types=1);

use Klimadashboard\Typo3\Service\Settings;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// TYPO3 v12+: allow the Klimadashboard embeds as frames when the frontend CSP is enabled.
// (TYPO3 v11 has no CSP API; add frame-src for the Klimadashboard domains to the site's own policy.)
$sources = array_map(
    static function (string $origin): UriValue {
        return new UriValue($origin);
    },
    GeneralUtility::makeInstance(Settings::class)->getAllowedOrigins()
);

return Map::fromEntries([
    Scope::frontend(),
    new MutationCollection(
        new Mutation(MutationMode::Extend, Directive::FrameSrc, ...$sources),
    ),
]);
