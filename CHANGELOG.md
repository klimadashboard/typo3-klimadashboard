# Changelog

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
