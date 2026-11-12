..  include:: /Includes.rst.txt

..  _privacy:

===============================
Privacy, accessibility and CSP
===============================

Privacy (DSGVO/GDPR)
====================

*   Neither the extension nor the embedded charts set cookies, and the extension
    itself loads no third-party resources: the stylesheet and the resize script
    are files of this extension.
*   The chart is loaded from klimadashboard.de/.at/.org (hosted in the EU). As
    with any embedded content, the visitor's browser transmits its IP address to
    Klimadashboard.
*   The embedded chart reports the view to Klimadashboard's own analytics
    (`analytics.klimadashboard.org`). This happens inside the iframe, on every
    view, and sets no cookies. Mention it in your privacy policy. The request
    comes from the embed document, so it is covered by the Klimadashboard site's
    own Content Security Policy, not by yours.
*   For sites that want consent first, enable :guilabel:`Load on click` per
    element or globally (`consent`). The chart is then only requested after the
    visitor clicks the button; the notice links to the privacy policy
    (`privacyUrl`).
*   The TYPO3 backend fetches the chart and region lists from the public
    Klimadashboard API server-side; no visitor or editor data is sent.
*   The live preview in the editing form is loaded by the editor's browser, so
    it reaches Klimadashboard like any other view of the chart. It only runs
    while an editor has the form open. Set `backendPreview` to off to disable
    it.

Accessibility (WCAG 2.1 AA)
===========================

*   Each iframe gets the chart title as accessible name (`title`) and the
    chart's language (`lang`).
*   The charts provide text alternatives and a table view.
*   The click-to-load button is keyboard-operable and moves focus to the chart
    after loading; colours meet the AA contrast ratio.

Content Security Policy
=======================

On TYPO3 v12 and later, the extension adds `frame-src` for the configured
Klimadashboard hosts to the frontend *and* backend policy
(:file:`Configuration/ContentSecurityPolicies.php`); the backend needs it for the
live preview. The resize script is a
local file, so `script-src 'self'` is sufficient. For TYPO3 v11 see
:ref:`installation`.
