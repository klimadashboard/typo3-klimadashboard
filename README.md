# Klimadashboard for TYPO3

TYPO3 extension (key `klimadashboard`, package `klimadashboard/typo3-klimadashboard`) that embeds
charts from [Klimadashboard](https://klimadashboard.org) (klimadashboard.de, .at, .org) as content elements.

- **Chart picker:** all published Klimadashboard charts, loaded from the public, read-only
  Klimadashboard API and cached (default 4 h, last good response kept if the API is down).
- **Settings per element:** Klimadashboard site (de/at/org), language (de/en or same as the page),
  region (regional associations, states, districts and independent cities, or any region ID),
  chart-specific options (e.g. *vehicle stock* vs. *new registrations*), design skin, hiding the explanatory text,
  automatic or fixed height, click-to-load.
- **Editors need no HTML rights.** The page module shows a preview with chart, region and skin.
- **Auto-height:** the chart's iframe reports its height through `postMessage`, and a small script in the
  extension resizes it (sender origin and frame are checked). No external scripts are loaded.
- **Accessibility (WCAG 2.1 AA):** the iframe gets the chart title as an accessible name and a `lang`
  attribute. The charts provide their own text alternatives and table views. The click-to-load button is
  keyboard-operable and moves focus to the chart after loading.
- **Privacy (DSGVO):** no cookies and no tracking. The chart is loaded from klimadashboard.* (EU hosting),
  which transmits the visitor's IP address to Klimadashboard. A two-click mode (optional, global or per
  element) loads the chart only after consent.
- **Content Security Policy:** on TYPO3 v12+ the extension adds `frame-src` for the Klimadashboard
  domains automatically (`Configuration/ContentSecurityPolicies.php`). On v11, add
  `frame-src https://klimadashboard.de https://klimadashboard.at https://klimadashboard.org` to your policy.

Compatible with **TYPO3 11.5 LTS, 12.4 LTS, 13.4 LTS and 14.3 LTS**, PHP 7.4–8.4.

| Frontend (auto-height) | Backend (chart tab) | Click-to-load |
|---|---|---|
| ![](Documentation/Images/frontend-demo.jpg) | ![](Documentation/Images/backend-chart-tab.jpg) | ![](Documentation/Images/click-to-load.jpg) |

**Test status:** tested end to end on **TYPO3 14.3 / PHP 8.4** and **TYPO3 11.5.41 / PHP 8.1** (DDEV setup
below): frontend, auto-height, click-to-load, editing form, saving, page module preview, new content element
wizard. 12.4 and 13.4 are covered by the same version switches but have not been run. The code passes PHP 7.4 lint.

### Known limitations (depend on klimadashboard-core)

- `/api/embed-options` and `/api/skins` are not on production yet. Until they are deployed, the chart options
  field offers no selects and the skin select shows the configured list. Options already stored on an element
  are listed and kept when it is saved.
- The `skin` parameter has no effect until the skin feature is deployed in core.
- `lang=en`: the embed route currently uses `lang` only for its server-rendered snapshot, so the interactive chart stays German.

## Installation

```bash
composer require klimadashboard/typo3-klimadashboard
```

Then include the TypoScript:

- **TYPO3 v13/v14:** add the site set *Klimadashboard* (`klimadashboard/klimadashboard`) to your site
  (Site Management > Sites > Sets), or as a dependency of your own site package set.
- **TYPO3 v11/v12:** include the static template *Klimadashboard* in your TypoScript record.

The extension requires `fluid_styled_content`: the element uses `lib.contentElement`, so header,
frames and layouts follow your site.

## Configuration

*Admin Tools > Settings > Extension Configuration > klimadashboard*

| Setting | Default | |
|---|---|---|
| `baseUrlDe` / `baseUrlAt` / `baseUrlOrg` | `https://klimadashboard.{de,at,org}` | Embed hosts, e.g. for a staging instance |
| `defaultSite` | `de` | Site used when an element has none set |
| `directusUrl` | `https://base.klimadashboard.org` | Public Klimadashboard data API (charts, regions) |
| `cacheLifetime` | `14400` | Cache lifetime of API responses (s) |
| `excludeChartTypes` | `test*` | Chart types hidden from the picker |
| `regionLayers` / `regionCountries` | `group,state,district` / `DE,AT` | Regions offered in the region select |
| `skins` | `metropolregion-nuernberg:…` | Skins offered (`id:Label`, comma separated) |
| `fetchSkins` | on | Also load the skin list from `GET <defaultSite>/api/skins` |
| `consent` | off | Two-click mode for all elements (elements can override) |
| `privacyUrl` | Klimadashboard privacy policy | Linked in the click-to-load notice |

Per site, use Page TSconfig, e.g. to preselect a skin and remove options:

```
TCAdefaults.tt_content.tx_klimadashboard_skin = metropolregion-nuernberg
TCEFORM.tt_content.tx_klimadashboard_site.removeItems = at,org
```

The colours of the click-to-load notice are CSS custom properties (`--klimadashboard-consent-bg`,
`--klimadashboard-button-bg`, …) in `Resources/Public/Css/embed.css` and can be overridden in the site CSS.

## How it works

Each element renders an iframe pointing at the existing Klimadashboard embed route:

```
https://klimadashboard.de/embed/<chartId>?auto=true&region=<regionId>&lang=de&skin=<skinId>&<chart options>
```

The extension uses these Klimadashboard endpoints (all public, read-only, no credentials):

| Endpoint | Used for |
|---|---|
| `base.klimadashboard.org/items/charts` | Chart picker and iframe titles |
| `base.klimadashboard.org/items/regions` | Region select |
| `klimadashboard.*/api/skins` | Skin select (see `docs/embed-skins.md` in klimadashboard-core) |
| `klimadashboard.*/api/embed-options/<chartId>` | Chart-specific options (see `docs/embed-options.md` in klimadashboard-core) |

If an endpoint is missing or unreachable, the extension falls back to the cached response or the
configured list. Existing elements keep their stored values.

## Local test environment (DDEV)

Requirements: [DDEV](https://ddev.com) with a Docker provider (e.g. Colima, OrbStack or Docker Desktop).
The DDEV project lives in the repository root. There are two TYPO3 composer projects, `dev/v14` (TYPO3 14.3,
PHP 8.4) and `dev/v11` (TYPO3 11.5, PHP 8.1, the version of the Metropolregion Nürnberg website). Both
install this extension from `../../` as a symlinked path repository, together with a minimal site package
(`dev/packages/demo_site`).

```bash
dev/setup.sh            # TYPO3 14.3: install, admin user, demo pages (re-run to reset)
dev/setup.sh 11         # switch to TYPO3 11.5 (resets the database)
ddev start              # later: start the version set up last
ddev stop               # stop the containers
ddev delete -O          # remove containers and database
```

`setup.sh` switches versions by writing `.ddev/config.local.yaml` (composer root, docroot, PHP version).
TYPO3 v11 is installed with `helhum/typo3-console` and includes the TypoScript through a template record;
v14 uses site sets.

> **v11 is for local testing only.** The last public 11.5 release (11.5.41) has security advisories that are
> fixed only in paid ELTS releases, so `dev/v11/composer.json` disables Composer's advisory block. Never deploy it.

The project uses the TLD `.localhost` (resolves to 127.0.0.1 without /etc/hosts or DNS changes);
`ddev describe` shows the exact port (here: 30000).

- Frontend: http://klimadashboard-typo3.localhost:30000/ (demo page), `/zwei-klick` (click-to-load), `/en/` (English)
- Backend: http://klimadashboard-typo3.localhost:30000/typo3/
- **Admin login:** `setup.sh` generates local-only test credentials and stores them in `dev/.env.local`
  (git-ignored). Read them with `cat dev/.env.local`.

The demo content is in `dev/demo/`. Clear the API cache with `ddev exec --dir /var/www/html/dev/v14 vendor/bin/typo3 cache:flush`
(v11: `--dir /var/www/html/dev/v11 vendor/bin/typo3cms cache:flush`).

## Publishing (not done yet)

- **Packagist:** push this repository to GitHub (`klimadashboard/typo3-klimadashboard`) and submit it on packagist.org.
- **TER:** register the extension key `klimadashboard` on extensions.typo3.org and upload a tag
  (e.g. with `tailor`). `.gitattributes` excludes `dev/` and `.ddev/` from the archive.

## License

GPL-2.0-or-later
