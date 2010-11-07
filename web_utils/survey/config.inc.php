<?

# ------- Program Setup ----------- #

$__module['name'] = 'survey';   // expanded to survey.module.php

$__module['db_create'] << __END__
CREATE TABLE `answers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `survey_id` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  `ipaddr` varchar(20) NOT NULL default '',
  `referer` varchar(255) NOT NULL default '',
  `marker` varchar(100) NOT NULL default '',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
CREATE TABLE `survey` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `thanks` text NOT NULL,
  `data` text NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `ip_check` tinyint(1) NOT NULL default '0',
  `valid_from` date NOT NULL default '0000-00-00',
  `valid_to` date NOT NULL default '9999-12-31',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
__END__;

?>