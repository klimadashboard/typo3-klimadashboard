-- Demo page tree and content for the Klimadashboard extension (TYPO3 v14)
DELETE FROM pages WHERE uid IN (1, 2, 3);
DELETE FROM tt_content WHERE pid IN (1, 2);

INSERT INTO pages (uid, pid, title, slug, doktype, is_siteroot, sorting, sys_language_uid, l10n_parent, tstamp, crdate)
VALUES
  (1, 0, 'Klimadashboard-Demo', '/', 1, 1, 256, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (2, 1, 'Zwei-Klick-Lösung', '/zwei-klick', 1, 0, 256, 0, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (3, 0, 'Klimadashboard demo', '/', 1, 1, 256, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO tt_content (pid, CType, header, bodytext, colPos, sorting, sys_language_uid,
  tx_klimadashboard_chart, tx_klimadashboard_site, tx_klimadashboard_language, tx_klimadashboard_region,
  tx_klimadashboard_skin, tx_klimadashboard_options, tx_klimadashboard_hidetext, tx_klimadashboard_height,
  tx_klimadashboard_consent, tstamp, crdate)
VALUES
  (1, 'text', 'Klimadashboard in TYPO3', '<p>Diese Seite zeigt Grafiken von klimadashboard.de, eingebunden mit dem Inhaltselement <strong>Klimadashboard-Grafik</strong>. Die Höhe passt sich automatisch an.</p>', 0, 100, 0,
   '', '', '', '', '', '', 0, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (1, 'klimadashboard_chart', 'PV-Ausbau in München', '', 0, 200, 0,
   '4c15342e-c329-4ab2-9748-205f35613a7e', 'de', '', '7d825a3e-dc1c-45da-9d71-b56b0061ab0e',
   '', '', 0, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (1, 'klimadashboard_chart', 'Pkw-Bestand nach Antriebsart (Option mode=bestand)', '', 0, 300, 0,
   '1b519edc-a120-4519-a008-0e0cec99fe91', 'de', '', '7d825a3e-dc1c-45da-9d71-b56b0061ab0e',
   '', 'mode=bestand', 0, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (1, 'klimadashboard_chart', 'Renewable share in Germany (English, without text)', '', 0, 400, 0,
   'c3e82a19-fc05-459c-9368-e28003ea5272', 'de', 'en', '',
   '', '', 1, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (1, 'klimadashboard_chart', 'Batteriespeicher in Bayern (feste Höhe 600 px)', '', 0, 500, 0,
   '8ae5880b-3fc0-49cb-9ab1-8575a205a9cc', 'de', '', 'faacc2ea-6e8a-47c8-a3c4-71bc58d26b09',
   '', '', 0, 600, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (2, 'klimadashboard_chart', 'Batteriespeicher in München (erst nach Klick laden)', '', 0, 100, 0,
   '8ae5880b-3fc0-49cb-9ab1-8575a205a9cc', 'de', '', '7d825a3e-dc1c-45da-9d71-b56b0061ab0e',
   '', '', 0, 0, 'on', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (1, 'klimadashboard_chart', 'Car drive types in Munich (follows page language)', '', 0, 100, 1,
   '1b519edc-a120-4519-a008-0e0cec99fe91', 'de', '', '7d825a3e-dc1c-45da-9d71-b56b0061ab0e',
   '', '', 0, 0, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
