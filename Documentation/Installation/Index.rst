..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

Install the extension
=====================

With Composer (recommended):

..  code-block:: bash

    composer require klimadashboard/typo3-klimadashboard

Without Composer, install the extension `klimadashboard` from the TYPO3
Extension Repository (TER) in the :guilabel:`Extensions` module.

Then update the database schema (:guilabel:`Admin Tools > Maintenance >
Analyze Database Structure`, or `vendor/bin/typo3 extension:setup`).

The extension requires `fluid_styled_content`. The content element uses
`lib.contentElement`, so header, frames and layouts follow your site.

Include the TypoScript
======================

TYPO3 v13 and v14
    Add the site set :guilabel:`Klimadashboard` (`klimadashboard/klimadashboard`)
    to your site in :guilabel:`Site Management > Sites`, or add it as a
    dependency of your own site package set:

    ..  code-block:: yaml
        :caption: config/sites/<site>/config.yaml

        dependencies:
          - klimadashboard/klimadashboard

TYPO3 v11 and v12
    Include the static template :guilabel:`Klimadashboard (klimadashboard)` in
    your TypoScript record, after :guilabel:`Fluid Content Elements`.

Content Security Policy on TYPO3 v11
====================================

TYPO3 v11 has no Content Security Policy API. If your site sends a CSP header
(for example from the web server), allow the Klimadashboard domains as frames:

..  code-block:: text

    frame-src https://klimadashboard.de https://klimadashboard.at https://klimadashboard.org

On TYPO3 v12 and later this is done automatically, see :ref:`privacy`.
