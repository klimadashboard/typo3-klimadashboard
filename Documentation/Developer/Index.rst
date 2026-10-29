..  include:: /Includes.rst.txt

..  _developer:

==============
For developers
==============

Rendering
=========

Each element renders an iframe pointing at the Klimadashboard embed route:

..  code-block:: text

    https://klimadashboard.de/embed/<chartId>?auto=true&region=<regionId>&lang=de&skin=<skinId>&<chart options>

With `auto=true` the embed reports its height via `postMessage`
(`{type: 'klimadashboard-resize', height}`). The script
:file:`Resources/Public/JavaScript/embed.js` resizes the matching iframe after
checking the sender's origin and window. Fixed-height elements omit `auto`.

The data processor `Klimadashboard\Typo3\DataProcessing\EmbedProcessor`
provides `{klimadashboard.url}`, `{klimadashboard.title}`,
`{klimadashboard.autoHeight}`, `{klimadashboard.consent}` and more to the
template.

APIs used
=========

All endpoints are public and read-only; responses are cached in the cache
`klimadashboard`.

..  list-table::
    :header-rows: 1

    *   -   Endpoint
        -   Used for
    *   -   `base.klimadashboard.org/items/charts`
        -   Chart picker and iframe titles
    *   -   `base.klimadashboard.org/items/regions`
        -   Region select
    *   -   `klimadashboard.*/api/skins`
        -   Skin select
    *   -   `klimadashboard.*/api/embed-options/<chartId>`
        -   Chart-specific options

Chart options are stored as a query string in
`tt_content.tx_klimadashboard_options`, e.g. `mode=bestand`.

Clear the API cache with :guilabel:`Flush all caches` or
`vendor/bin/typo3 cache:flush`.
