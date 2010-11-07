# phpMyAdmin MySQL-Dump
# http://phpwizard.net/phpMyAdmin/
#
# Serveur: localhost Base de données: phpdig

# --------------------------------------------------------
#
# Structure de la table 'includes'
#
CREATE TABLE includes (
   in_id mediumint(11) NOT NULL auto_increment,
   in_site_id mediumint(9) NOT NULL,
   in_path text NOT NULL,
   PRIMARY KEY (in_id),
   UNIQUE KEY in_id (in_id),
   KEY in_site_id (in_site_id)
);
