-- phpMyAdmin SQL Dump
-- version 2.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 27, 2006 at 03:23 PM
-- Server version: 5.0.19
-- PHP Version: 5.1.0
--
-- Database: `whatsnew_dcbot`
--

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `tth` char(39) NOT NULL,
  `share_path` text NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `size` bigint(10) NOT NULL,
  `last_nick` varchar(40) default NULL,
  `first_found_time` int(10) unsigned NOT NULL,
  `last_found_time` int(10) unsigned NOT NULL,
  `dc_file_type` smallint(6) default NULL,
  PRIMARY KEY  (`tth`),
  KEY `dc_file_type` (`dc_file_type`),
  KEY `size` (`size`),
  FULLTEXT KEY `share_path` (`share_path`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
