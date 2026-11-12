# Changelog

## 1.1.0 – 2026-11-12

### Added

- **Live preview in the editing form.** The *Chart* tab shows the selected chart as it will appear on
  the site and follows the form while editing: chart, site, language, region, chart options, skin,
  hidden explanatory text and fixed height. Nothing is saved to show it, and the preview can be
  switched off with the new `backendPreview` setting.
- Content-Security-Policy `frame-src` for the Klimadashboard domains is now also added to the backend
  scope on TYPO3 v12+, which the preview needs.

### Fixed

- The *Appearance* tab of the content element kept its label on TYPO3 11.5 and 12.4. Those versions
  have no `appearance` entry in the core tab labels, so the tab was unnamed and its fields (frame,
  spacing, section index, link to top) ended up at the bottom of the *Chart* tab.
- The chart, region and design skin selects stay in the editing form on TYPO3 14 when their list is
  empty, e.g. while the Klimadashboard API is unreachable on a cold cache (`showIfEmpty`). TYPO3 14
  otherwise removes a select without selectable items from the form.
- `fetchSkins` is off by default: `/api/skins` is not deployed on the Klimadashboard sites yet and
  answers 404.
- Documentation: the embed reports the view to Klimadashboard's own analytics
  (`analytics.klimadashboard.org`); this is now stated in the privacy chapter, the README and the
  click-to-load notice instead of claiming that the charts do not track. No cookies are set.

## 1.0.1 – 2026-10-29

- No design skins are preconfigured any more; skins come from Klimadashboard (`/api/skins`) or the
  `skins` setting.
- Neutral example content in the documentation and screenshots.

## 1.0.0 – 2026-10-29

First release.

- Content element "Klimadashboard chart" for TYPO3 11.5, 12.4, 13.4 and 14.3.
- Chart picker and region select loaded from the public Klimadashboard API and cached; the last good
  response is used if the API is unreachable.
- Per element: Klimadashboard site (de/at/org), language, region, chart-specific options
  (`/api/embed-options`), design skin (`/api/skins` plus configured list), hiding the explanatory text,
  automatic or fixed height, click-to-load.
- Auto-height via `postMessage`, with sender origin and frame checks; no external scripts.
- Optional two-click mode (global or per element).
- Content-Security-Policy `frame-src` for the Klimadashboard domains on TYPO3 v12+.
- Page module preview, new content element wizard entry, German and English labels.
