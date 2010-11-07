# phpMyAdmin MySQL-Dump
# http://phpwizard.net/phpMyAdmin/
#
# Serveur: localhost Base de données: phpdig

# --------------------------------------------------------
#
# Structure de la table 'clicks'
#
CREATE TABLE clicks (
  c_num mediumint(9) NOT NULL,
  c_url varchar(255) NOT NULL default '',
  c_val varchar(255) NOT NULL default '',
  c_time timestamp(14) NOT NULL
);

# --------------------------------------------------------
#
# Structure de la table 'site_page'
#
CREATE TABLE site_page (
  site_id int(4) NOT NULL,
  num_page int(4) default '0',
  PRIMARY KEY (site_id)
);

# --------------------------------------------------------
#
# Structure de la table 'sites_days_upd'
#
CREATE TABLE sites_days_upd (
  site_id int(4) NOT NULL,
  days int(4) default '0',
  PRIMARY KEY (site_id)
);
