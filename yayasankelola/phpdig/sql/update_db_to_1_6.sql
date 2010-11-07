# Update from the 1.4.x versions
# Add the table prefix if needed
# ---------------------------------

# DONT'T FORGET THIS :
ALTER TABLE engine DROP INDEX spider_id;
ALTER TABLE spider ADD filesize INT(11) DEFAULT 0 NOT NULL;
ALTER TABLE sites ADD locked tinyint(1) DEFAULT 0 NOT NULL;

ALTER TABLE tempspider ADD UNIQUE INDEX ( id );
ALTER TABLE keywords ADD UNIQUE INDEX ( key_id );
ALTER TABLE spider ADD UNIQUE INDEX ( spider_id );
ALTER TABLE sites ADD UNIQUE INDEX ( site_id );
ALTER TABLE excludes ADD UNIQUE INDEX ( ex_id );

# --------------------------------------------------------
#
# Structure de la table 'logs'
#
CREATE TABLE logs (
  l_id mediumint(9) NOT NULL auto_increment,
  l_includes varchar(255) NOT NULL default '',
  l_excludes varchar(127) default NULL,
  l_num mediumint(9) default NULL,
  l_mode char(1) default NULL,
  l_ts timestamp(14) NOT NULL,
  PRIMARY KEY  (l_id),
  UNIQUE KEY l_id (l_id),
  KEY l_includes (l_includes),
  KEY l_excludes (l_excludes)
);
