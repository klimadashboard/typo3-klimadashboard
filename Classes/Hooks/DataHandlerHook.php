<?php

declare(strict_types=1);

namespace Klimadashboard\Typo3\Hooks;

use Klimadashboard\Typo3\Service\EmbedUrlBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Stores the chart options posted by EmbedOptionsElement (an array) as a query string.
 */
class DataHandlerHook
{
    /**
     * @param array<string, mixed> $incomingFieldArray
     * @param string|int $id
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void
    {
        if ($table !== 'tt_content' || !isset($incomingFieldArray['tx_klimadashboard_options'])
            || !is_array($incomingFieldArray['tx_klimadashboard_options'])) {
            return;
        }
        $options = [];
        foreach ($incomingFieldArray['tx_klimadashboard_options'] as $key => $value) {
            if (is_string($value) && $value !== '' && preg_match('/^[a-zA-Z0-9_-]+$/', (string)$key)
                && !in_array($key, EmbedUrlBuilder::RESERVED_PARAMETERS, true)) {
                $options[(string)$key] = $value;
            }
        }
        ksort($options);
        $incomingFieldArray['tx_klimadashboard_options'] = http_build_query($options, '', '&', PHP_QUERY_RFC3986);
    }
}
