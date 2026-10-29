<?php

declare(strict_types=1);

use Klimadashboard\Typo3\Form\Element\EmbedOptionsElement;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Information\Typo3Version;

// FormEngine nodes: TYPO3 v13+ creates them through DI (setData() + injected NodeFactory),
// v11/v12 pass NodeFactory and data to the constructor and must not autowire them.
return static function (ContainerConfigurator $container): void {
    if ((new Typo3Version())->getMajorVersion() >= 13) {
        $container->services()
            ->set(EmbedOptionsElement::class)
            ->autowire()
            ->autoconfigure()
            ->public()
            ->share(false);
    }
};
