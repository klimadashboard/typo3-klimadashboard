..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

The extension adds the content element **Klimadashboard chart**. It embeds any
published chart of Klimadashboard, a non-profit open data platform with climate,
energy and mobility data for more than 13,000 regions in Germany and Austria.

Features
========

*   **Chart picker**: all published charts, loaded from the Klimadashboard API and
    cached. If the API is unreachable, the last good response is used.
*   **Per element**: Klimadashboard site (de/at/org), language, region,
    chart-specific options, design skin, hiding the explanatory text, automatic or
    fixed height and click-to-load.
*   **Automatic height**: the chart reports its height, and the iframe adapts.
*   **No HTML rights** needed for editors; the page module shows a preview.
*   **Privacy**: no cookies, no tracking, optional two-click mode.
*   **Content Security Policy**: the allowance for the Klimadashboard domains is
    added automatically on TYPO3 v12+.

Supported versions
==================

..  list-table::
    :header-rows: 1

    *   -   TYPO3
        -   PHP
    *   -   11.5 LTS
        -   7.4 – 8.3
    *   -   12.4 LTS
        -   8.1 – 8.4
    *   -   13.4 LTS
        -   8.2 – 8.4
    *   -   14.3 LTS
        -   8.2 – 8.4

Screenshots
===========

..  figure:: /Images/frontend-demo.jpg
    :alt: A Klimadashboard chart embedded in a TYPO3 page

    A chart in the frontend; the height adapts to the content.

..  figure:: /Images/backend-chart-tab.jpg
    :alt: The Chart tab of the content element in the TYPO3 backend

    The Chart tab of the content element.

..  figure:: /Images/backend-page-module.jpg
    :alt: Preview of Klimadashboard charts in the page module

    Preview in the page module.

..  figure:: /Images/click-to-load.jpg
    :alt: Privacy notice with a button to load the chart

    Optional click-to-load notice.
