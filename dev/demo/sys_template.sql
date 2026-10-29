-- TYPO3 v11/v12 have no site sets: include the TypoScript through a template record
DELETE FROM sys_template WHERE pid = 1;
INSERT INTO sys_template (pid, title, root, clear, include_static_file, config, tstamp, crdate)
VALUES (1, 'Klimadashboard demo', 1, 3,
  'EXT:fluid_styled_content/Configuration/TypoScript/,EXT:klimadashboard/Configuration/TypoScript',
  '@import ''EXT:demo_site/Configuration/TypoScript/setup.typoscript''',
  UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
