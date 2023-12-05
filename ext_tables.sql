CREATE TABLE tx_powermail_domain_model_mail (
	tx_powermailadvanceddoi_postdoiactions int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_powermailadvanceddoi_postdoiaction (
	type varchar(255) DEFAULT '' NOT NULL,
	mail int(11) unsigned DEFAULT '0' NOT NULL,
	done_at int(11) unsigned DEFAULT '0' NOT NULL,
	notice text
);
