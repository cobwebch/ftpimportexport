#
# Table structure for table 'tx_ftpimport_records'
#
CREATE TABLE tx_ftpimportexport_records (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	transfer_type varchar(6) DEFAULT 'import' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	source_path varchar(255) DEFAULT '' NOT NULL,
	target_path varchar(255) DEFAULT '' NOT NULL,
	pattern varchar(255) DEFAULT '' NOT NULL,
	ftp_host varchar(255) DEFAULT '' NOT NULL,
	ftp_user varchar(255) DEFAULT '' NOT NULL,
	ftp_password varchar(255) DEFAULT '' NOT NULL,
	post_processing varchar(6) DEFAULT '' NOT NULL,
	post_processing_path varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
) ENGINE=InnoDB;