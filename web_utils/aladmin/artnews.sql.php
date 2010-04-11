# phpMyAdmin MySQL-Dump
# version 2.5.1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Sep 07, 2004 at 07:59 AM
# Server version: 3.23.42
# PHP Version: 4.3.8
# Database : `artnews`
# --------------------------------------------------------

#
# Table structure for table `an_kinerja_keuangan_tab`
#
# Creation: Sep 05, 2004 at 10:17 PM
# Last update: Sep 05, 2004 at 10:17 PM
#

CREATE TABLE `an_kinerja_keuangan_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `proyek_id` bigint(20) NOT NULL default '0',
  `tahun` varchar(25) NOT NULL default '',
  `account` varchar(25) NOT NULL default '',
  `amount` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `an_kinerja_keuangan_tab`
#

# --------------------------------------------------------

#
# Table structure for table `an_periode_tab`
#
# Creation: Sep 05, 2004 at 09:47 PM
# Last update: Sep 05, 2004 at 10:47 PM
#

CREATE TABLE `an_periode_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `periode` varchar(25) NOT NULL default '',
  `tahun` varchar(25) NOT NULL default '',
  `deskripsi` varchar(255) NOT NULL default '0',
  `tgl_mulai` date NOT NULL default '0000-00-00',
  `tgl_selesai` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Dumping data for table `an_periode_tab`
#

INSERT INTO `an_periode_tab` VALUES (1, 'maret', '2004', 'Ada deh hu hu hu', '2004-12-12', '2004-12-12');
# --------------------------------------------------------

#
# Table structure for table `an_proyek_progres_kerjasama_detail_tab`
#
# Creation: Sep 05, 2004 at 10:17 PM
# Last update: Sep 05, 2004 at 10:17 PM
#

CREATE TABLE `an_proyek_progres_kerjasama_detail_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `proyek_id` bigint(20) NOT NULL default '0',
  `tahun` varchar(25) NOT NULL default '',
  `periode` varchar(25) NOT NULL default '',
  `tahapan_id` bigint(20) NOT NULL default '0',
  `progres_value` varchar(25) NOT NULL default '',
  `tgl_rencana` varchar(25) NOT NULL default '',
  `tgl_realisasi` varchar(25) NOT NULL default '',
  `periode_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  KEY `proyek_id` (`proyek_id`),
  KEY `periode_id` (`periode_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `an_proyek_progres_kerjasama_detail_tab`
#

# --------------------------------------------------------

#
# Table structure for table `an_proyek_progres_kerjasama_tab`
#
# Creation: Sep 05, 2004 at 09:53 PM
# Last update: Sep 05, 2004 at 09:53 PM
#

CREATE TABLE `an_proyek_progres_kerjasama_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `proyek_id` bigint(20) NOT NULL default '0',
  `tahun` varchar(25) NOT NULL default '',
  `periode` varchar(25) NOT NULL default '',
  `keterangan` text NOT NULL,
  `permasalahan` text NOT NULL,
  `tindak_lanjut` text NOT NULL,
  `tgl_realisasi` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `an_proyek_progres_kerjasama_tab`
#

# --------------------------------------------------------

#
# Table structure for table `an_proyek_tab`
#
# Creation: Sep 05, 2004 at 09:42 PM
# Last update: Sep 05, 2004 at 09:42 PM
#

CREATE TABLE `an_proyek_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `deskripsi` tinytext NOT NULL,
  `nama` varchar(25) NOT NULL default '',
  `kerjasama_id` bigint(20) NOT NULL default '0',
  `panjang_ruas` varchar(25) NOT NULL default '',
  `keterangan_seksi` varchar(255) NOT NULL default '',
  `investor` varchar(100) NOT NULL default '',
  `masa_konsesi` varchar(25) NOT NULL default '',
  `masa_konstruksi` varchar(25) NOT NULL default '',
  `biaya_investigasi` varchar(25) NOT NULL default '',
  `saham_disetor` varchar(25) NOT NULL default '',
  `personil` varchar(55) NOT NULL default '',
  `permasalahan` text NOT NULL,
  `tindak_lanjut` tinytext NOT NULL,
  `pm_jabatan` varchar(100) NOT NULL default '',
  `pm_nama` varchar(100) NOT NULL default '',
  `ruas_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  KEY `ruas_id` (`ruas_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `an_proyek_tab`
#

# --------------------------------------------------------

#
# Table structure for table `an_ruasjalan_tab`
#
# Creation: Sep 05, 2004 at 09:32 PM
# Last update: Sep 06, 2004 at 11:28 PM
#

CREATE TABLE `an_ruasjalan_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `nama` varchar(75) NOT NULL default '',
  `panjang_jalan` varchar(255) NOT NULL default '',
  `sistem_operasi` varchar(25) NOT NULL default '',
  `kecepatan_rencana` varchar(25) NOT NULL default '',
  `daerah_milik_jalan` varchar(25) NOT NULL default '',
  `penampang_melintang` text NOT NULL,
  `jenis_perkerasan` varchar(25) NOT NULL default '',
  `simpang_susun` text NOT NULL,
  `deskripsi` text NOT NULL,
  PRIMARY KEY  (`rowid`),
  KEY `nama` (`nama`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Dumping data for table `an_ruasjalan_tab`
#

INSERT INTO `an_ruasjalan_tab` VALUES (1, 'Jalan Jagorawi', '5', 'o', 'k', 'd', 'a', 'a', 'a', 'a');
# --------------------------------------------------------

#
# Table structure for table `an_tahapan_kerjasama_tab`
#
# Creation: Sep 05, 2004 at 10:04 PM
# Last update: Sep 05, 2004 at 10:04 PM
#

CREATE TABLE `an_tahapan_kerjasama_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `kerjasama_id` bigint(20) NOT NULL default '0',
  `deskripsi` varchar(255) NOT NULL default '',
  `index` int(11) NOT NULL default '0',
  `kategori` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `an_tahapan_kerjasama_tab`
#


