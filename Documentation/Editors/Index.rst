..  include:: /Includes.rst.txt

..  _editors:

===========
For editors
===========

Insert a chart
==============

#.  In the :guilabel:`Page` (or :guilabel:`Layout`) module, click
    :guilabel:`+ Content` and choose :guilabel:`Klimadashboard chart` (under
    :guilabel:`Plugins`).
#.  Optionally enter a header on the :guilabel:`General` tab.
#.  Open the :guilabel:`Chart` tab and choose a chart. Save to see the options
    of the chosen chart.
#.  The :guilabel:`Preview` at the bottom of the tab shows the chart as it will
    appear on the site, and follows the fields above while you edit.

Preview
=======

The :guilabel:`Preview` shows the selected chart with the settings currently in
the form — you do not have to save first. :guilabel:`Reload preview` fetches it
again, :guilabel:`Open in a new tab` opens the embed on Klimadashboard.

The preview is shown at a fixed height. On the site the height adapts to the
chart unless :guilabel:`Fixed height (px)` is set. The chart is loaded from
klimadashboard.de/.at/.org with your browser; an administrator can switch the
preview off with the `backendPreview` setting.

Fields on the Chart tab
=======================

..  list-table::
    :header-rows: 1
    :widths: 25 75

    *   -   Field
        -   Meaning
    *   -   Klimadashboard site
        -   klimadashboard.de, .at or .org. Some charts exist only on one site;
            the chart list adapts after saving.
    *   -   Language
        -   Language of the chart; by default the language of the page.
    *   -   Chart
        -   The chart to embed. The list is loaded from Klimadashboard.
    *   -   Region
        -   Preselects a region (state, district, independent city …) for charts
            with regional data.
    *   -   Other region (ID)
        -   Any other Klimadashboard region ID, e.g. a municipality. Takes
            precedence over :guilabel:`Region`.
    *   -   Chart options
        -   Settings the selected chart offers, e.g. *vehicle stock* or *new
            registrations*. "Default" uses the chart's own default.
    *   -   Design skin
        -   Fonts, accent colour and logos of the embed, e.g. in the corporate
            design of your organisation.
    *   -   Hide explanatory text
        -   Shows only the chart, without the text above it.
    *   -   Fixed height (px)
        -   `0` (default) lets the height adapt automatically.
    *   -   Load on click
        -   Shows a privacy notice and loads the chart only after a click.
            "Default" follows the extension configuration.
    *   -   Preview
        -   The chart as it will appear on the site, see above. Not a setting.

The page module shows the chart title, region and skin of each element, with a
link to the chart on Klimadashboard.
