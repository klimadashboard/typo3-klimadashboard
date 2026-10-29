CREATE TABLE tt_content (
	tx_klimadashboard_chart varchar(64) DEFAULT '' NOT NULL,
	tx_klimadashboard_site varchar(16) DEFAULT '' NOT NULL,
	tx_klimadashboard_language varchar(8) DEFAULT '' NOT NULL,
	tx_klimadashboard_region varchar(64) DEFAULT '' NOT NULL,
	tx_klimadashboard_region_custom varchar(64) DEFAULT '' NOT NULL,
	tx_klimadashboard_skin varchar(64) DEFAULT '' NOT NULL,
	tx_klimadashboard_options text,
	tx_klimadashboard_hidetext smallint(5) unsigned DEFAULT '0' NOT NULL,
	tx_klimadashboard_height int(11) unsigned DEFAULT '0' NOT NULL,
	tx_klimadashboard_consent varchar(8) DEFAULT '' NOT NULL
);
