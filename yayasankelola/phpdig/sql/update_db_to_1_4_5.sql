# Update from the 1.4.4 versions
# ---------------------------------
CREATE TABLE excludes (
   ex_id mediumint(11) NOT NULL auto_increment,
   ex_site_id mediumint(9),
   ex_path text NOT NULL,
   PRIMARY KEY (ex_id),
   KEY ex_site_id (ex_site_id)
);

ALTER TABLE tempspider ADD INDEX ( site_id );


