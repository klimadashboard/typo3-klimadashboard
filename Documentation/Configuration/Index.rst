..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

Extension configuration
=======================

:guilabel:`Admin Tools > Settings > Extension Configuration > klimadashboard`

..  list-table::
    :header-rows: 1
    :widths: 25 30 45

    *   -   Setting
        -   Default
        -   Description
    *   -   `baseUrlDe`, `baseUrlAt`, `baseUrlOrg`
        -   `https://klimadashboard.de` …
        -   Hosts the charts are embedded from, e.g. for a staging instance.
    *   -   `defaultSite`
        -   `de`
        -   Site used when an element has none set.
    *   -   `directusUrl`
        -   `https://base.klimadashboard.org`
        -   Public Klimadashboard data API (charts, regions).
    *   -   `cacheLifetime`
        -   `14400`
        -   Cache lifetime of API responses in seconds.
    *   -   `excludeChartTypes`
        -   `test*`
        -   Chart types hidden from the chart select (comma separated,
            wildcards allowed).
    *   -   `regionLayers`
        -   `group,state,district`
        -   Region levels offered in the region select.
    *   -   `regionCountries`
        -   `DE,AT`
        -   Countries offered in the region select.
    *   -   `skins`
        -   (empty)
        -   Additional design skins, as `id:Label`, comma separated, e.g.
            `my-skin:My organisation`.
    *   -   `fetchSkins`
        -   on
        -   Also load the list of skins from Klimadashboard.
    *   -   `consent`
        -   off
        -   Load charts only after a click by default.
    *   -   `privacyUrl`
        -   Klimadashboard privacy policy
        -   Linked in the click-to-load notice.

Page TSconfig
=============

All fields are normal TCA fields, so the usual Page TSconfig applies. Preselect
a skin and the site, and remove options editors should not use:

..  code-block:: typoscript
    :caption: EXT:my_sitepackage/Configuration/page.tsconfig

    TCAdefaults.tt_content {
        tx_klimadashboard_skin = my-skin
        tx_klimadashboard_site = de
    }
    TCEFORM.tt_content.tx_klimadashboard_site.removeItems = at,org

Styling
=======

The click-to-load notice uses CSS custom properties, which you can override in
your site CSS:

..  code-block:: css

    .klimadashboard-embed {
        --klimadashboard-consent-bg: #f3f4f6;
        --klimadashboard-consent-color: #1f2937;
        --klimadashboard-button-bg: #1f6f5c;
        --klimadashboard-button-color: #ffffff;
        --klimadashboard-focus: #1f2937;
    }

The template can be overridden with a higher
`tt_content.klimadashboard_chart.templateRootPaths` entry; the template name is
`KlimadashboardChart`.
