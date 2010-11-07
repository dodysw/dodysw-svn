# Update from the 1.0.x versions
# ---------------------------------
ALTER TABLE tempspider ADD upddate TIMESTAMP , ADD error TINYINT DEFAULT '0' not null;
ALTER TABLE spider CHANGE md5 md5 VARCHAR (50);

# Update from the <= 1.4.3 versions
# ---------------------------------
ALTER TABLE keywords ADD twoletters CHAR (2) NOT NULL AFTER key_id;
UPDATE keywords set twoletters = substring(keyword from 1 for 2);
ALTER TABLE keywords ADD INDEX(twoletters);

# Update from the <= 1.4.4 versions
# ---------------------------------
ALTER TABLE sites ADD username CHAR (32) , ADD password CHAR (32), ADD port SMALLINT

