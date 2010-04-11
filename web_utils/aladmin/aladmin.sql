CREATE TABLE `an_account_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `account_no` varchar(4) NOT NULL default '',
  `description` varchar(30) NOT NULL default '',
  `parent_account` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`account_no`)
) TYPE=MyISAM;

CREATE TABLE `an_article_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `summary` text NOT NULL,
  `body` text NOT NULL,
  `cat_id` varchar(255) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

CREATE TABLE `an_bahan_lapisan_permukaan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_bahan_lapisan_permukaan` varchar(10) NOT NULL default '',
  `deskripsi` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_bahan_lapisan_permukaan`)
) TYPE=MyISAM;

CREATE TABLE `an_cabang_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `nama_cabang` varchar(30) NOT NULL default '',
  `keterangan` varchar(255) NOT NULL default '',
  `kode_propinsi` char(2) NOT NULL default '',
  `main` int(11) NOT NULL default '0',
  `akses` int(11) NOT NULL default '0',
  `status_pengelola` varchar(255) NOT NULL default '',
  `status` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`)
) TYPE=MyISAM;

CREATE TABLE `an_category_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `cat_id` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`cat_id`)
) TYPE=MyISAM;

INSERT INTO `an_category_tab` VALUES (1,'sport','Sport','World Sport');
INSERT INTO `an_category_tab` VALUES (2,'worldnews','World News','World News');
CREATE TABLE `an_gerbang_tol_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `kode_gerbang` varchar(4) NOT NULL default '',
  `gerbang` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`,`kode_gerbang`)
) TYPE=MyISAM;

CREATE TABLE `an_investasi_balsheet_in_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_proyek` varchar(6) NOT NULL default '',
  `kode_cabang` varchar(4) NOT NULL default '',
  `tahun_in` int(11) NOT NULL default '0',
  `bulan_in` varchar(255) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_proyek`,`kode_cabang`,`tahun_in`,`bulan_in`)
) TYPE=MyISAM;

CREATE TABLE `an_investasi_balsheet_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_proyek` varchar(6) NOT NULL default '',
  `account_no` varchar(4) NOT NULL default '',
  `tahun_in` int(11) NOT NULL default '0',
  `bulan_in` varchar(255) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_proyek`,`account_no`,`tahun_in`,`bulan_in`)
) TYPE=MyISAM;

CREATE TABLE `an_jalan_lateral_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `sta_awal` varchar(17) NOT NULL default '',
  `sta_akhir` varchar(17) NOT NULL default '',
  `lebar` varchar(15) NOT NULL default '',
  `kode_bahan_lapisan_permukaan` varchar(10) NOT NULL default '',
  `kode_lapisan_perkerasan` varchar(10) NOT NULL default '',
  `kode_tipe_lapisan_permukaan` varchar(10) NOT NULL default '',
  `tipe_perkerasan` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`,`sta_awal`)
) TYPE=MyISAM;

CREATE TABLE `an_jalan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `jalan` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`)
) TYPE=MyISAM;

CREATE TABLE `an_kategori_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_kategori` varchar(4) NOT NULL default '',
  `kategori` varchar(55) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

CREATE TABLE `an_kecelakaan_penyebab_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `sta_awal` varchar(17) NOT NULL default '',
  `arah_ruas` int(11) NOT NULL default '0',
  `jumlah` int(11) NOT NULL default '0',
  `kode_penyebab_kecelakaan` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`,`tahun_op`,`bulan_op`,`sta_awal`,`arah_ruas`)
) TYPE=MyISAM;

CREATE TABLE `an_kecelakaan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `sta_awal` varchar(17) NOT NULL default '',
  `arah_ruas` int(11) NOT NULL default '0',
  `jumlah` int(11) NOT NULL default '0',
  `tingkat_kecelakaan` double NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`,`tahun_op`,`bulan_op`,`sta_awal`,`arah_ruas`)
) TYPE=MyISAM;

CREATE TABLE `an_kinerja_keuangan_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `proyek_id` bigint(20) NOT NULL default '0',
  `tahun` varchar(25) NOT NULL default '',
  `account` varchar(25) NOT NULL default '',
  `amount` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

CREATE TABLE `an_kota_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_propinsi` char(2) NOT NULL default '',
  `kode_kota` varchar(7) NOT NULL default '',
  `nama_kota` varchar(45) NOT NULL default '',
  `luas_wilayah` varchar(25) NOT NULL default '',
  `jumlah_penduduk` varchar(25) NOT NULL default '',
  `status_kota` char(1) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_propinsi`,`kode_kota`)
) TYPE=MyISAM;

INSERT INTO `an_kota_tab` VALUES (1,'DK','JKT','Jakarta','140000','110000','2');
CREATE TABLE `an_lapisan_perkerasan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_lapisan_perkerasan` varchar(10) NOT NULL default '',
  `deskripsi` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_lapisan_perkerasan`)
) TYPE=MyISAM;

CREATE TABLE `an_operasional_balsheet_in_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_proyek` varchar(4) NOT NULL default '',
  `kode_cabang` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_proyek`,`kode_cabang`,`tahun_op`,`bulan_op`)
) TYPE=MyISAM;

CREATE TABLE `an_operasional_balsheet_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `account_no` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`account_no`,`tahun_op`,`bulan_op`)
) TYPE=MyISAM;

CREATE TABLE `an_penyebab_kecelakaan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `penyebab_kecelakaan` varchar(10) NOT NULL default '',
  `deskripsi` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`penyebab_kecelakaan`)
) TYPE=MyISAM;

CREATE TABLE `an_periode_in_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `tahun` int(11) NOT NULL default '0',
  `bulan` varchar(255) NOT NULL default '',
  `deskripsi` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

INSERT INTO `an_periode_in_tab` VALUES (1,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (2,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (12,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (4,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (5,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (6,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (7,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (8,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (9,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (10,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (11,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (13,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (14,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (15,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (16,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (17,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (18,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (19,0,'','');
INSERT INTO `an_periode_in_tab` VALUES (20,2000,'12','deskripsi');
INSERT INTO `an_periode_in_tab` VALUES (21,9000,'91','11');
CREATE TABLE `an_periode_op_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `tahun` int(11) NOT NULL default '0',
  `bulan` varchar(255) NOT NULL default '',
  `deskripsi` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

CREATE TABLE `an_periode_tab` (
  `rowid` bigint(20) NOT NULL auto_increment,
  `periode` varchar(25) NOT NULL default '',
  `tahun` varchar(25) NOT NULL default '',
  `deskripsi` varchar(255) NOT NULL default '0',
  `tgl_mulai` date NOT NULL default '0000-00-00',
  `tgl_selesai` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

INSERT INTO `an_periode_tab` VALUES (1,'maret','2004','Ada deh hu hu hu','2004-12-12','2004-12-12');
CREATE TABLE `an_progres_kerjasama_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_proyek` varchar(6) NOT NULL default '',
  `kode_tahapan` varchar(6) NOT NULL default '',
  `kode_kerjasama` varchar(4) NOT NULL default '',
  `kategori1` varchar(4) NOT NULL default '',
  `kategori2` varchar(4) NOT NULL default '',
  `tahun_in` int(11) NOT NULL default '0',
  `bulan_in` varchar(45) NOT NULL default '',
  `nilai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_proyek`,`kode_tahapan`,`kode_kerjasama`,`kategori1`,`kategori2`,`tahun_in`,`bulan_in`)
) TYPE=MyISAM;

CREATE TABLE `an_progress_kerjasama_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `tahapan` int(11) NOT NULL default '0',
  `proyek_id` int(11) NOT NULL default '0',
  `valid_from` date NOT NULL default '0000-00-00',
  `persentase` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`)
) TYPE=MyISAM;

INSERT INTO `an_progress_kerjasama_tab` VALUES (1,1,2,'0000-00-00',4);
CREATE TABLE `an_propinsi_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_propinsi` char(2) NOT NULL default '',
  `nama_propinsi` varchar(45) NOT NULL default '',
  `luas_wilayah` varchar(25) NOT NULL default '',
  `jumlah_penduduk` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_propinsi`)
) TYPE=MyISAM;

INSERT INTO `an_propinsi_tab` VALUES (1,'DK','DKI Jakarta','20000','1100000');
INSERT INTO `an_propinsi_tab` VALUES (3,'BA','Banda Aceh','204200','1000000');
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
) TYPE=MyISAM;

INSERT INTO `an_proyek_progres_kerjasama_detail_tab` VALUES (1,1,'2','3',5,'6','7','8',9);
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
) TYPE=MyISAM;

CREATE TABLE `an_proyek_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_proyek` varchar(4) NOT NULL default '',
  `kode_cabang` varchar(4) NOT NULL default '',
  `nama` varchar(35) NOT NULL default '',
  `deskripsi` varchar(255) NOT NULL default '',
  `kode_kerjasama` varchar(255) NOT NULL default '',
  `keterangan_seksi` varchar(255) NOT NULL default '',
  `panjang` varchar(10) NOT NULL default '',
  `investor` varchar(50) NOT NULL default '',
  `masa_konsesi` varchar(25) NOT NULL default '',
  `masa_konstruksi` varchar(25) NOT NULL default '',
  `biaya_investigasi` varchar(25) NOT NULL default '',
  `saham_disetor` varchar(25) NOT NULL default '',
  `personil` varchar(45) NOT NULL default '',
  `permasalahan` varchar(255) NOT NULL default '',
  `tindak_lanjut` varchar(255) NOT NULL default '',
  `pm_jabatan` varchar(45) NOT NULL default '',
  `pm_nama` varchar(45) NOT NULL default '',
  `pengelola` varchar(45) NOT NULL default '',
  `status_tahap` varchar(45) NOT NULL default '',
  `status` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_proyek`)
) TYPE=MyISAM;

CREATE TABLE `an_ruas_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `ruas` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`)
) TYPE=MyISAM;

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
) TYPE=MyISAM;

INSERT INTO `an_ruasjalan_tab` VALUES (1,'Jalan Jagorawi','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (2,'Merak-Serang','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (3,'Jalan Jagorawi','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (4,'Merak-Serang','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (5,'Merak-Serang','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (6,'Merak-Serang','5','o','k','d','a','a','a','a');
INSERT INTO `an_ruasjalan_tab` VALUES (7,'Merak-Serang','5','o','k','d','a','a','a','a');
CREATE TABLE `an_sys_manage_modules_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `module_id` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `table_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`module_id`)
) TYPE=MyISAM;

INSERT INTO `an_sys_manage_modules_tab` VALUES (351,'account','Account','an_account_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (352,'admfp','Frontpage','');
INSERT INTO `an_sys_manage_modules_tab` VALUES (353,'article','Artikel','an_article_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (354,'bahan_lapisan_permukaan','Bahan Lapisan Permukaan','an_bahan_lapisan_permukaan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (355,'cabang','Cabang','an_cabang_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (356,'category','Category','an_category_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (357,'gerbang_tol','Gerbang Tol','an_gerbang_tol_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (358,'investasi_balsheet','Neraca Investasi','an_investasi_balsheet_in_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (359,'jalan','Jalan','an_jalan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (360,'jalan_lateral','Jalan Lateral','an_jalan_lateral_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (361,'kecelakaan','Kecelakaan','an_kecelakaan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (362,'kecelakaan_penyebab','Kecelakaan Penyebab','an_kecelakaan_penyebab_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (363,'kota','Kota','an_kota_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (364,'lapisan_perkerasan','Lapisan Perkerasan','an_lapisan_perkerasan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (365,'login','Login','');
INSERT INTO `an_sys_manage_modules_tab` VALUES (366,'logout','Login','');
INSERT INTO `an_sys_manage_modules_tab` VALUES (367,'manage_modules','Manage modules','an_sys_manage_modules_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (368,'operasional_balsheet','Neraca Operasional','an_operasional_balsheet_in_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (369,'penyebab_kecelakaan','Penyebab Kecelakaan','an_penyebab_kecelakaan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (370,'periode','Periode Kerjasama','an_periode_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (371,'periode_in','Periode Investasi','an_periode_in_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (372,'periode_op','Periode Operasional','an_periode_op_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (373,'phpinfo','Frontpage','');
INSERT INTO `an_sys_manage_modules_tab` VALUES (374,'progres_kerjasama','Progres Kerjasama','an_progres_kerjasama_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (375,'progres_kerjasama_value','Histori Progres Kerjasama','an_progres_kerjasama_value_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (376,'progress_kerjasama','Progress Kerjasama','an_progress_kerjasama_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (377,'propinsi','Propinsi','an_propinsi_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (378,'proyek','Proyek','an_proyek_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (379,'proyek_progress','Progress Proyek','an_proyek_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (380,'ruas','Ruas','an_ruas_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (381,'ruasjalan','Ruas jalan tol','an_ruasjalan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (382,'send_emails','','an_send_emails_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (383,'tahapan_kerjasama','Tahapan Kerjasama','an_tahapan_kerjasama_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (384,'tipe_kerjasama','Tipe Kerjasama','an_tipe_kerjasama_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (385,'tipe_lapisan_permukaan','Tipe Lapisan Permukaan','an_tipe_lapisan_permukaan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (386,'tipe_perkerasan','Tipe Perkerasan','an_tipe_perkerasan_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (387,'usrmgr','User manager','an_user_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (388,'volume_lalulintas_realisasi','Volume Lalu Lintas Realisasi','an_volume_lalulintas_realisasi_tab');
INSERT INTO `an_sys_manage_modules_tab` VALUES (389,'volume_lalulintas_rencana','Volume Lalu Lintas Rencana','an_volume_lalulintas_rencana_tab');
CREATE TABLE `an_tahapan_kerjasama_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_tahapan` varchar(6) NOT NULL default '',
  `tahapan` varchar(45) NOT NULL default '',
  `kode_kerjasama` varchar(4) NOT NULL default '',
  `kategori1` varchar(4) NOT NULL default '',
  `kategori2` varchar(4) NOT NULL default '',
  `urutan` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_tahapan`,`kode_kerjasama`,`kategori1`,`kategori2`)
) TYPE=MyISAM;

CREATE TABLE `an_tipe_kerjasama_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_kerjasama` varchar(4) NOT NULL default '',
  `kerjasama` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_kerjasama`)
) TYPE=MyISAM;

CREATE TABLE `an_tipe_lapisan_permukaan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_tipe_lapisan_permukaan` varchar(10) NOT NULL default '',
  `deskripsi` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_tipe_lapisan_permukaan`)
) TYPE=MyISAM;

CREATE TABLE `an_tipe_perkerasan_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `tipe_perkerasan` varchar(10) NOT NULL default '',
  `deskripsi` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`tipe_perkerasan`)
) TYPE=MyISAM;

CREATE TABLE `an_user_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(25) default NULL,
  `password` varchar(50) default NULL,
  `level` tinyint(3) unsigned default NULL,
  `group` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `username` (`username`)
) TYPE=MyISAM;

INSERT INTO `an_user_tab` VALUES (23,'admin','202cb962ac59075b964b07152d234b70',1,'');
INSERT INTO `an_user_tab` VALUES (45,'dody','202cb962ac59075b964b07152d234b70',2,'');
INSERT INTO `an_user_tab` VALUES (48,'mimi','dde6ecd6406700aa000b213c843a3091',1,'');
CREATE TABLE `an_volume_lalulintas_realisasi_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `kode_jalan` varchar(4) NOT NULL default '',
  `kode_ruas` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `kode_gerbang_asal` varchar(4) NOT NULL default '',
  `kode_gerbang_tujuan` varchar(4) NOT NULL default '',
  `gol_I` int(11) NOT NULL default '0',
  `gol_IIA` int(11) NOT NULL default '0',
  `gol_IIB` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`kode_jalan`,`kode_ruas`,`tahun_op`,`bulan_op`,`kode_gerbang_asal`,`kode_gerbang_tujuan`)
) TYPE=MyISAM;

CREATE TABLE `an_volume_lalulintas_rencana_tab` (
  `rowid` int(10) unsigned NOT NULL auto_increment,
  `kode_cabang` varchar(4) NOT NULL default '',
  `tahun_op` int(11) NOT NULL default '0',
  `bulan_op` varchar(255) NOT NULL default '',
  `total` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rowid`),
  UNIQUE KEY `key_1` (`kode_cabang`,`tahun_op`,`bulan_op`)
) TYPE=MyISAM;


