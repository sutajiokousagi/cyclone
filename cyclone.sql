-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2012 at 01:44 AM
-- Server version: 5.1.40
-- PHP Version: 5.2.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cyclone`
--
DROP DATABASE `cyclone`;
CREATE DATABASE `cyclone` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cyclone`;

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
CREATE TABLE IF NOT EXISTS `actions` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `module_id` int(11) NOT NULL,
  `action_name` varchar(64) NOT NULL,
  `action_alias` varchar(32) NOT NULL,
  `action_params` varchar(64) DEFAULT NULL,
  `action_description` text,
  PRIMARY KEY (`action_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `action_enabled`, `module_id`, `action_name`, `action_alias`, `action_params`, `action_description`) VALUES
(1, 1, 10, 'echo', 'echo', 'text', 'Print to the console environment where the  timer is executed'),
(2, 1, 10, 'email', 'email', 'to|subject|message', 'Send an email to someone with a subject & a message'),
(3, 1, 2, 'tweet', 'tweet', 'text', 'Post a tweet'),
(4, 1, 3, 'update status', 'update_status', 'message|link', 'Update your Facebook status'),
(5, 1, 12, 'switch on', 'digital_out_on', 'channel', 'Switch on a digital output channel'),
(6, 1, 12, 'switch off', 'digital_out_off', 'channel', 'Switch off a digital output channel'),
(7, 1, 12, 'display text on LCD', 'lcd_text', 'text', 'Show a simple text on LCD'),
(8, 1, 12, 'motor stop', 'motor_stop', 'channel', 'Stop a motor channel'),
(9, 1, 12, 'motor forward', 'motor_forward', 'channel|speed', 'Set a motor channel to move forward with a given speed'),
(10, 1, 12, 'motor backward', 'motor_backward', 'channel|speed', 'Set a motor channel to move backward with a given speed');

-- --------------------------------------------------------

--
-- Table structure for table `configs`
--

DROP TABLE IF EXISTS `configs`;
CREATE TABLE IF NOT EXISTS `configs` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `config_name` varchar(64) NOT NULL,
  `config_param` text NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `configs`
--

INSERT INTO `configs` (`config_id`, `module_id`, `user_id`, `config_name`, `config_param`, `last_updated`) VALUES
(11, 3, 1, 'oauth_token', 'AAAGKvS0WfAcBAHQv1Y4criUgLJ22bw07dnM3uXRSAS9LZAfM8CD86DgK0Oga9e1P316OBmKJ6ZAinnZAGnDJFyx7ZCSlgQWU6QR4JXOYKwZDZD', '2012-05-14 01:20:00'),
(12, 2, 1, 'oauth_token_secret', 'BkrsHuGDVEw6tPe1xCYKCwqmHcXg7aN4Ea1Q8Gebpg', '2012-05-14 01:23:10'),
(13, 2, 1, 'user_id', '277880346', '2012-05-14 01:23:10'),
(14, 2, 1, 'screen_name', 'torinnguyen', '2012-05-14 01:23:10'),
(17, 2, 1, 'oauth_token', '277880346-pSPZhSNGUibulpcH33HYM1uVugBx9QHH06tsXMG9', '2012-05-14 01:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `event_param` text,
  `event_status` int(11) NOT NULL DEFAULT '0',
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1245 ;

--
-- Dumping data for table `events`
--

-- No data required for this table

-- --------------------------------------------------------

--
-- Table structure for table `events_async`
--

DROP TABLE IF EXISTS `events_async`;
CREATE TABLE IF NOT EXISTS `events_async` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `event_param` text,
  `event_status` int(11) NOT NULL DEFAULT '0',
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
CREATE TABLE IF NOT EXISTS `filters` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `module_id` int(11) NOT NULL,
  `filter_name` varchar(64) NOT NULL,
  `filter_alias` varchar(32) NOT NULL,
  `filter_params` varchar(64) DEFAULT NULL,
  `filter_type` int(11) NOT NULL DEFAULT '0' COMMENT '0: string, 1: number, 2: datetime',
  `filter_description` text,
  PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `filters`
--

INSERT INTO `filters` (`filter_id`, `filter_enabled`, `module_id`, `filter_name`, `filter_alias`, `filter_params`, `filter_type`, `filter_description`) VALUES
(1, 1, 9, 'contains string', 'contains', 'string', 0, NULL),
(2, 1, 9, 'starts with string', 'starts_with', 'string', 0, NULL),
(3, 1, 9, 'ends with string', 'ends_with', 'string', 0, NULL),
(4, 1, 9, 'equals (=)', 'equals', 'string/number', 1, NULL),
(5, 1, 9, 'greater than (>)', 'greater', 'number', 1, NULL),
(6, 1, 9, 'greater or equals to (>=)', 'greater_equal', 'number', 1, NULL),
(7, 1, 9, 'less than (<)', 'less', 'number', 1, NULL),
(8, 1, 9, 'less than or equals to (<=)', 'less_equal', 'number', 1, NULL),
(9, 1, 9, 'within range', 'within', 'from|to', 1, NULL),
(10, 1, 9, 'before date/time', 'before_datetime', 'date/time', 2, NULL),
(11, 1, 9, 'after date/time', 'after_datetime', 'date/time', 2, NULL),
(12, 1, 9, 'between date/time', 'between_datetime', 'from|to', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `module_name` varchar(64) NOT NULL,
  `module_alias` varchar(64) NOT NULL,
  `module_type` int(11) NOT NULL DEFAULT '2' COMMENT '0: standalone, 1: hardware, 2: software api',
  `module_role` int(11) NOT NULL DEFAULT '0' COMMENT '0: triggers, 1: filters, 2: actions, 3: hybrid',
  `module_description` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`module_id`, `module_enabled`, `module_name`, `module_alias`, `module_type`, `module_role`, `module_description`) VALUES
(1, 1, 'Scheduler', 'scheduler', 0, 0, 'A module that generates triggers at user-defined schedule'),
(2, 0, 'Twitter', 'twitter', 2, 3, 'A module that generates triggers based on user''s Twitter events'),
(3, 0, 'Facebook', 'facebook', 2, 3, 'A module that generates triggers based on user''s Facebook events'),
(4, 1, 'RSS', 'rss', 0, 0, 'A module that generates triggers based on content changes of an RSS feed'),
(5, 1, 'HDMI', 'hdmi', 1, 0, 'A module that generates triggers based on HDMI hardware events'),
(6, 1, 'Network', 'network', 1, 0, 'A module that generates triggers based on network hardware events'),
(7, 1, 'GMail', 'gmail', 2, 0, 'A module that generates triggers based on user''s GMail events'),
(8, 1, 'HelloTrigger', 'hellotrigger', 0, 0, 'A simple HelloWorld module with sample triggers'),
(9, 1, 'HelloFilter', 'hellofilter', 0, 1, 'A simple HelloFilter module with basic string, number & datetime processing'),
(10, 1, 'HelloAction', 'helloaction', 0, 2, 'A simple HelloAction module with basic sample actions'),
(11, 1, 'iOS', 'ios', 0, 0, 'A module that generates triggers based on iOS device event'),
(12, 1, 'Hardware', 'kovan', 1, 2, 'A placeholder module for Kovan platform');

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

DROP TABLE IF EXISTS `preferences`;
CREATE TABLE IF NOT EXISTS `preferences` (
  `preference_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preference_name` varchar(255) NOT NULL,
  `preference_value` text NOT NULL,
  `preference_description` varchar(255) DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`preference_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

--
-- Dumping data for table `preferences`
--

INSERT INTO `preferences` (`preference_id`, `user_id`, `preference_name`, `preference_value`, `preference_description`, `last_updated`) VALUES
(1, 1, 'refresh_interval', '5', 'This is just a testing key-value pair', '0000-00-00 00:00:00'),
(62, 1, '11_previous_location', '{"longitude":"103.844007","latitude":"1.343378","accuracy":"6989.000000"}', NULL, '2012-04-12 19:00:04'),
(61, 1, '11_current_location', '{"longitude":"103.844554","latitude":"1.341742","accuracy":"1414.000000"}', NULL, '2012-04-23 20:54:49'),
(68, 1, '4_previous_feed_httpwwwengadgetcomrssxml', '[{"title":"Samsung rumored to tweak Galaxy Note 10.1 inside and out"},{"title":"Nokia World broken up into smaller events, bumped up to September 5-6"},{"title":"Incantor brings World of Warcraft to real life (hands-on)"},{"title":"MIT''s LiquiGlide could spell the end of slow-moving ketchup nightmares (video)"},{"title":"NTT DoCoMo bids for 700MHz spectrum, will drop two big-ones if it succeeds"},{"title":"Delorme''s inReach two-way satellite communicator gets iOS support, sends iPhone texts from Timbuktu (video)"},{"title":"Army spy blimp to launch within weeks: 300 feet long, $500 million, ''multi-intelligent''"},{"title":"NHK working on Hybridcast interactive TV platform (video)"},{"title":"NVIDIA outlines Kai platform, hopes to make good on quad-core $199 tablet promise"},{"title":"Douglas Coupland''s V-Pole unifies wireless connectivity and EV charging in an LED streetlight"},{"title":"Casio Japan says its new Exilim EX-ZR300 compact camera is fast and furious"},{"title":"Kyocera Hydro bares all for the FCC"},{"title":"Lenovo beats PC market with 46 percent profit surge"},{"title":"Acer Iconia A510 Olympic Tab to launch in UK next month"},{"title":"Robo-fish swim into the ocean''s funk, so you don''t have to"},{"title":"RAMPAGE 6 notepad runs Android 2.3 in a rugged package"},{"title":"Hillcrest Labs takes its TV motion control system to China, becomes TCL''s new best friend"},{"title":"Microsoft details Windows 8''s pre-boot world, helps you skip the F8 F8 F8 routine"},{"title":"Mobile security researchers present Android Malware Genome Project at IEEE"},{"title":"Researchers power microbots made of bubbles with lasers"},{"title":"PSA: Windows Phone Marketplace now requires Windows Phone 7.5"},{"title":"Engineer Guy shows how a phone accelerometer works, knows what''s up and sideways (video)"},{"title":"Engadget HD Podcast 300 - 05.22.2012"},{"title":"Ion launches Air Pro WiFi, helps you document your morning commute (video)"},{"title":"VIA Technologies outs $49 APC Android barebones, nods towards Raspberry Pi"},{"title":"Frontline investigates the cause of cell tower deaths tonight (video)"},{"title":"Dragon Drive! lets you take the wheel, while Nuance takes dictation"},{"title":"NASA app goes 2.0, the safest launch this century"},{"title":"NPD Q1 2012: Apple still king of the mobile computing hill thanks to iPad"},{"title":"Dell profit drops 33 percent in Q1, both home and corporate sales take a hit"},{"title":"3D printing gets more flexible with Nylon extrusion"},{"title":"Cheap \\u00a3149 PC and broadband bundle gives the UK something to smile about"},{"title":"Scientists develop rewritable digital storage built into DNA; biological binary exists"},{"title":"Samsung Chromebox gets a premature outing, $330 price tag (video)"},{"title":"Verizon''s ZTE-built Jetpack 890L 4G hotspot ships May 24th, promises globetrotting for $20"},{"title":"Meta Watch announces new dev kit with added iOS support, Bluetooth 4.0"},{"title":"Xfinity Voice 2Go bridges the gap between mobile, VoIP and home phones"},{"title":"Scientists use metal and silicon to create invisibility cloak (no, you can''t wear it)"},{"title":"HTC refreshes Android update timeline, details which devices won''t get Ice Cream Sandwich"},{"title":"Sony-made Google TV units will come with Plex as standard"}]', NULL, '2012-05-23 22:45:10'),
(91, 1, '3_previous_feed_Torin Nguyen', '[{"id":"566862027_326894150711952","message":"one command to rule them all   rm -rf *","link":"http:\\/\\/thenextweb.com\\/media\\/2012\\/05\\/21\\/how-pixars-toy-story-2-was-deleted-twice-once-by-technology-and-again-for-its-own-good\\/","name":"How Pixar\\u2019s Toy Story 2 was deleted twice, once by technology and again for its own good"},{"id":"566862027_410967945602238","message":"The idea of having a walkie talkie spanning the world is awesome","link":"http:\\/\\/zello.com\\/","name":"Zello. Instant talk."},{"id":"566862027_414326498587428","message":"random job title generator ","link":"http:\\/\\/www.bullshitjob.com\\/title\\/","name":"Bullshit Job   \\u00bb Job Title Generator"}]', NULL, '2012-05-23 22:45:08'),
(83, 0, 'system_ext_callbackUrl_facebook', 'http://cyclone.torinnguyen.com/ext_callback_facebook.php', NULL, '2012-05-12 00:00:00'),
(69, 0, 'system_ext_callbackUrl_twitter', 'http://cyclone.torinnguyen.com/ext_callback_twitter.php', NULL, '2012-04-26 00:00:00'),
(70, 0, 'system_ext_consumerKey_twitter', 'Ab7Iba57LHmhNJAO9BWstg', NULL, '2012-04-26 00:00:00'),
(71, 0, 'system_ext_consumerSecret_twitter', 'd4OuPNkJHHLMKcMzPhtf9MBSbdo9EG6jPeclYbvrg', NULL, '2012-04-26 00:00:00'),
(81, 1, '2_previous_follower_my_followers', '[{"created_at":"","screen_name":"leveret2708"},{"created_at":"","screen_name":"CaseMorton"},{"created_at":"","screen_name":"trananhduc88"},{"created_at":"","screen_name":"erain9"},{"created_at":"","screen_name":"alannqt"},{"created_at":"","screen_name":"sufsuf82"},{"created_at":"","screen_name":"kartasutanto"},{"created_at":"","screen_name":"tranducduy"}]', NULL, '2012-05-23 22:45:05'),
(82, 1, '2_previous_timeline_ntluan', '[{"date":"Tue May 01 05:03:33 +0000 2012","text":"unicode error? http:\\/\\/t.co\\/NreV2awp","screen_name":"ntluan"},{"date":"Mon Apr 30 08:22:31 +0000 2012","text":"this.is.amazing #bluesky #sun #airplane #cloudy #silhouette http:\\/\\/t.co\\/GNLN5mxL","screen_name":"ntluan"},{"date":"Sat Apr 28 14:51:44 +0000 2012","text":"confused about design decision for AVPlayer class. there is really no way to control volume on the fly?! #ios #Programming","screen_name":"ntluan"},{"date":"Sat Apr 28 13:59:48 +0000 2012","text":"iphone can connect to hotel wifi, laptop cannot. le me login to router. raise max IP pool. voil\\u00e0","screen_name":"ntluan"},{"date":"Sat Apr 28 07:51:02 +0000 2012","text":"#roadtrip  @ NSE Plus Highway 150KM http:\\/\\/t.co\\/ChqaXPHr","screen_name":"ntluan"},{"date":"Fri Apr 27 07:18:44 +0000 2012","text":"beautiful cloudless day http:\\/\\/t.co\\/rqvzN9zp","screen_name":"ntluan"},{"date":"Fri Apr 27 03:51:33 +0000 2012","text":"I set my fees high because dumb people can''t afford and don''t appreciate the effort","screen_name":"ntluan"},{"date":"Fri Apr 27 03:26:22 +0000 2012","text":"@kamal is it easy to integrate with existing simple apps? what does @andycroll have to say?","screen_name":"ntluan"},{"date":"Fri Apr 27 02:40:48 +0000 2012","text":"@andycroll i use the lightweight Foxit. But not having a pdf reader or Chrome when opening an urgent email is like FFFFFFFFUUUUUUU","screen_name":"ntluan"},{"date":"Thu Apr 26 14:00:13 +0000 2012","text":"@_3lion haha ok fine ~ but u need a pdf reader more than painting with mouse","screen_name":"ntluan"},{"date":"Thu Apr 26 13:54:52 +0000 2012","text":"it''s 2012 and Windows still doesn''t have a built-in PDF reader","screen_name":"ntluan"},{"date":"Thu Apr 26 13:11:57 +0000 2012","text":"why the fuck does it run everytime i start AND shutdown #windows http:\\/\\/t.co\\/1AHscjXu","screen_name":"ntluan"},{"date":"Thu Apr 19 17:08:52 +0000 2012","text":"@satyamag tab 10.1, but this ''feature'' is on all Android 3.x","screen_name":"ntluan"},{"date":"Thu Apr 19 11:57:26 +0000 2012","text":"the worst UI gesture ever not only on Android http:\\/\\/t.co\\/R8cLlbIW","screen_name":"ntluan"},{"date":"Thu Apr 19 02:55:59 +0000 2012","text":"@honcheng haha yeah. i was thinking UI thou","screen_name":"ntluan"},{"date":"Thu Apr 19 02:43:59 +0000 2012","text":"@_3lion been there! totally true!","screen_name":"ntluan"},{"date":"Thu Apr 19 02:33:59 +0000 2012","text":"BlackBerry vs Android is like Craglist vs eBay","screen_name":"ntluan"}]', NULL, '2012-05-01 18:10:03'),
(74, 1, '2_previous_timeline_torinnguyen', '[{"date":"Sun May 06 07:39:25 +0000 2012","text":"SMUWorld: The World is our campus!  http:\\/\\/t.co\\/Xgw744Y6 http:\\/\\/t.co\\/kLW1gNqm","screen_name":"torinnguyen"},{"date":"Tue May 01 05:30:03 +0000 2012","text":"Yay! New follower. Hi there  @leveret2708","screen_name":"torinnguyen"},{"date":"Tue May 01 05:03:43 +0000 2012","text":"Tweet-shadowing @ntluan unicode error? http:\\/\\/t.co\\/dfkMIaqb","screen_name":"torinnguyen"},{"date":"Thu Apr 12 11:00:13 +0000 2012","text":"Auto-tweet by Cyclone. Reaching home...242.687073783m","screen_name":"torinnguyen"},{"date":"Thu Apr 12 02:10:17 +0000 2012","text":"Auto-tweet by Cyclone. Reaching office...113.171440228m","screen_name":"torinnguyen"},{"date":"Thu Apr 05 20:55:07 +0000 2012","text":"Auto-tweet with Cyclone  (http:\\/\\/t.co\\/ClAatzey) at 4:55am when I clearly should be sleeping :)","screen_name":"torinnguyen"},{"date":"Fri Mar 23 13:01:22 +0000 2012","text":"I love Dropbox because I can loose that few grams when not carrying a thumbdrive around http:\\/\\/t.co\\/PwOcvARv","screen_name":"torinnguyen"},{"date":"Sat Mar 17 14:53:10 +0000 2012","text":"Sociality - Premium Social Media Icon Set (CSS3 &amp; PNG) http:\\/\\/t.co\\/kh5GlA3A via @designmodo","screen_name":"torinnguyen"},{"date":"Fri Mar 09 10:33:29 +0000 2012","text":"Costa Concordia cruise ship runs aground by The Travel Magazine http:\\/\\/203.126.215.76\\/u\\/http:\\/\\/203.126.215.76\\/u\\/5wsnjq @ via Aurora","screen_name":"torinnguyen"},{"date":"Sun Jan 08 08:15:42 +0000 2012","text":"@iSMU Interactive Kiosk is launching soon. Exciting.","screen_name":"torinnguyen"},{"date":"Thu Oct 13 04:19:28 +0000 2011","text":"tweet from ChumbyOne","screen_name":"torinnguyen"},{"date":"Sun Oct 02 05:26:23 +0000 2011","text":"@kartasutanto yea baby. U buying?","screen_name":"torinnguyen"},{"date":"Wed Sep 21 15:26:56 +0000 2011","text":"Check out this app on your iPad http:\\/\\/t.co\\/zX217amJ","screen_name":"torinnguyen"},{"date":"Mon Sep 12 09:24:53 +0000 2011","text":"#bunniestudios &lt;font size=\\"10\\"&gt;HUGE TEXT&lt;\\/font&gt;","screen_name":"torinnguyen"},{"date":"Mon Sep 12 09:22:59 +0000 2011","text":"#bunniestudios The quick brown fox jumps over the lazy dog","screen_name":"torinnguyen"},{"date":"Wed Apr 20 13:25:34 +0000 2011","text":"where got?","screen_name":"torinnguyen"},{"date":"Sat Apr 16 05:02:35 +0000 2011","text":"@kartasutanto yo!. noob here. sign up account for fun only","screen_name":"torinnguyen"}]', NULL, '2012-05-23 22:45:02'),
(84, 0, 'system_ext_consumerKey_facebook', 'Ab7Iba57LHmhNJAO9BWstg', NULL, '2012-05-12 00:00:00'),
(85, 0, 'system_ext_consumerSecret_facebook', 'd4OuPNkJHHLMKcMzPhtf9MBSbdo9EG6jPeclYbvrg', NULL, '2012-05-12 00:00:00'),
(86, 0, 'system_ext_appid_facebook', '434020086610951', NULL, '2012-05-12 00:00:00'),
(87, 0, 'system_ext_appsecret_facebook', '9df5f69549775f4d4da8719e5343f81b', NULL, '2012-05-12 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `rules`
--

DROP TABLE IF EXISTS `rules`;
CREATE TABLE IF NOT EXISTS `rules` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_id` int(11) NOT NULL,
  `trigger_param` text,
  `user_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL COMMENT '0: no filter, >0: filter_id',
  `filter_param` text,
  `action_id` int(11) NOT NULL,
  `action_param` text,
  `rule_description` text,
  `rule_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

--
-- Dumping data for table `rules`
--

INSERT INTO `rules` (`rule_id`, `trigger_id`, `trigger_param`, `user_id`, `filter_id`, `filter_param`, `action_id`, `action_param`, `rule_description`, `rule_enabled`, `last_updated`) VALUES
(2, 17, NULL, 1, 0, NULL, 1, '{"text":"blah blah blah. this is great!"}', NULL, 0, '2012-02-16 00:31:00'),
(3, 18, NULL, 1, 0, NULL, 1, '{"text":"this is a simple ''echo'' action to print out this line. this is awesome!"}', NULL, 0, '2012-02-16 00:31:00'),
(14, 21, '{"datetime":"2012-04-06 02:47"}	', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Test one-shot trigger","message":"hello!"}', NULL, 1, '2012-03-26 11:09:39'),
(15, 20, '{"days":"mon,tue,wed,thu,fri","time":"05:10"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Testing weekly trigger","message":"Testing"}', NULL, 1, '2012-03-21 23:51:52'),
(16, 19, '{"time":"05:15"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Testing daily trigger","message":"Testing "}', NULL, 1, '2012-03-21 23:51:52'),
(19, 21, '{"datetime":"2012-04-06 04:55"}', 1, 0, NULL, 3, '{"text":"Auto-tweet with Cyclone  (http://cyclone.torinnguyen.com/ui_rules.php) at 4:55am when I clearly should be sleeping :)"}', NULL, 1, '2012-04-06 02:06:28'),
(28, 26, '{"url":"http://www.engadget.com/rss.xml"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Engadget RSS feed has [count] new article(s)","message":"Engadget RSS feed [url] <br>has [count] new article(s). <br/>Titles: [titles]"}', 'Inform me by email when Engadget has new article', 1, '2012-04-09 02:49:46'),
(23, 23, '{"distance":"300","longitude":"103.8455021","latitude":"1.3417876"}', 1, 0, NULL, 3, '{"text":"Auto-tweet by Cyclone. Reaching home...[distance]m"}', 'Send a tweet when I''m reaching home', 1, '2012-04-08 22:10:57'),
(24, 23, '{"distance":"200","longitude":"103.844193","latitude":"1.277278"}', 1, 0, NULL, 3, '{"text":"Auto-tweet by Cyclone. Reaching office...[distance]m"}', 'Send a tweet when I''m reaching my office', 1, '2012-04-08 22:23:12'),
(33, 14, '{"screen_name":"ntluan"}', 1, 0, NULL, 3, '{"text":"Tweet-shadowing @[username] [tweet1]"}', 'Monitor someone''s tweet and retweet the same thing, with mention his/her screen name', 0, '2012-05-01 01:19:43'),
(34, 15, NULL, 1, 0, NULL, 3, '{"text":"Yay! New follower. Hi there  @[follower1]"}', 'Monitor my followers list and tweet a greeting at him/her', 1, '2012-05-01 01:19:43'),
(42, 21, '{"datetime":"2012-05-15 01:25"}', 1, 0, NULL, 4, '{"message":"testing","link":"http://www.google.com"}', NULL, 1, '2012-05-15 01:10:07'),
(36, 21, '{"datetime":"2012-05-15 00:01"}', 1, 0, NULL, 4, '{"message":"testing","link":"http://www.google.com"}', NULL, 1, '2012-05-14 23:59:20'),
(43, 1, NULL, 1, 0, NULL, 4, '{"message":"Testing: HDMI cable is connected","link":"http://cyclone.torinnguyen.com"}', NULL, 1, '2012-05-16 01:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `smtp`
--

DROP TABLE IF EXISTS `smtp`;
CREATE TABLE IF NOT EXISTS `smtp` (
  `smtp_config_id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(128) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_secure` varchar(32) DEFAULT NULL,
  `smtp_name` varchar(128) NOT NULL,
  `smtp_from_email` varchar(128) NOT NULL,
  `smtp_username` varchar(128) NOT NULL,
  `smtp_password` varchar(128) NOT NULL,
  `smtp_config_enable` tinyint(4) NOT NULL DEFAULT '0',
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`smtp_config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `smtp`
--

INSERT INTO `smtp` (`smtp_config_id`, `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_name`, `smtp_from_email`, `smtp_username`, `smtp_password`, `smtp_config_enable`, `last_updated`) VALUES
(1, 'mail.torinnguyen.com', 587, '.', 'Cyclone', 'cyclone@torinnguyen.com', 'cyclone+torinnguyen.com', 'd46df44cfc456d22737f04166bb7ff01', 1, '2012-05-25 01:19:17'),
(5, 'smtp.gmail.com', 465, 'ssl', 'Cyclone', 'cyclone@gmail.com', 'torinnguyen@gmail.com', 'q1w2e3r4Q!W@E#R$', 0, '2012-04-05 03:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `triggers`
--

DROP TABLE IF EXISTS `triggers`;
CREATE TABLE IF NOT EXISTS `triggers` (
  `trigger_id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `module_id` int(11) NOT NULL,
  `trigger_name` varchar(64) NOT NULL,
  `trigger_alias` varchar(32) NOT NULL,
  `trigger_params` varchar(64) DEFAULT NULL,
  `trigger_description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`trigger_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `triggers`
--

INSERT INTO `triggers` (`trigger_id`, `trigger_enabled`, `module_id`, `trigger_name`, `trigger_alias`, `trigger_params`, `trigger_description`) VALUES
(1, 1, 5, 'connect', 'connect', 'datetime', 'Triggered when HDMI cable is plugged in'),
(2, 1, 5, 'disconnect', 'disconnect', 'datetime', 'Triggered when HDMI cable is unplugged'),
(3, 1, 5, 'resolution change', 'resolution_change', 'old_resolution|new_resolution', 'Triggered when HDMI input signal changes resolution'),
(4, 1, 6, 'connect', 'connect', NULL, 'Triggered when network got connected'),
(5, 1, 6, 'disconnect', 'disconnect', NULL, 'Triggered when network is disconnected'),
(6, 1, 6, 'change ip', 'change ip', NULL, 'Triggered when IP Address changes'),
(7, 1, 7, 'new email', 'new email', 'from|subject|message', 'Triggered when a new email is received'),
(8, 1, 3, 'got tagged', 'got_tagged', NULL, 'Triggered when someone tagged out in a photo'),
(9, 1, 3, 'status change', 'status_change', NULL, 'Triggered when your status changes'),
(10, 1, 3, 'new post', 'new_post', NULL, 'Triggered when you post a new link post'),
(11, 1, 3, 'upload new photo', 'upload_new_photo', NULL, 'Triggered when you upload a new photo'),
(12, 1, 3, 'profile change', 'profile_change', NULL, 'Triggered when your profile changes'),
(13, 1, 2, 'new tweet from you', 'new tweet from you', NULL, 'Triggered when you post a new tweet'),
(14, 1, 2, 'new tweet from someone', 'new tweet from someone', 'screen_name', 'Triggered when a specific person post a new tweet'),
(15, 1, 2, 'new follower', 'new follower', NULL, 'Triggered when you have a new follower'),
(16, 1, 2, 'mentioned', 'mentioned', NULL, 'Triggered when you are mentioned in a tweet'),
(17, 1, 8, 'time is on even minute', 'even_minute', NULL, 'Triggered when the time is on even minutes'),
(18, 1, 8, 'time is on odd minute', 'odd_minute', NULL, 'Triggered when the time is on odd minutes'),
(19, 1, 1, 'daily', 'daily', 'time', 'Triggered daily at a specific time'),
(20, 1, 1, 'weekly', 'weekly', 'days|time', 'Triggered weekly on a specific day and time'),
(21, 1, 1, 'once', 'once', 'datetime', 'Triggered once on a specific date and time'),
(22, 1, 11, 'stays within location', 'within_location', 'distance|longitude|latitude', 'Triggered as long as device''s location is within range'),
(23, 1, 11, 'enter location', 'enter_location', 'distance|longitude|latitude', 'Triggered when device''s location just enters the given range'),
(24, 1, 11, 'leave location', 'leave_location', 'distance|longitude|latitude', 'Triggered when device''s location just leaves the given range'),
(25, 1, 11, 'stays within a location for xxx minutes', 'within_location_minute', 'distance|longitude|latitude|minutes', 'Triggered as long as device''s location is within range for longer than given time'),
(26, 1, 4, 'new RSS entry', 'rss_new', 'url', 'Triggered when there is a new entry in RSS feed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) NOT NULL,
  `user_email` varchar(64) DEFAULT NULL,
  `user_password` varchar(64) DEFAULT NULL,
  `user_memo` varchar(128) DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_memo`, `last_updated`) VALUES
(1, 'ACD49441-44BA-6D95-84B4-E6E3AF40CC84', NULL, NULL, 'Torin''s development unit', '2012-02-10 23:40:00'),
(2, 'C4B7D503-F338-5238-A822-8AE1E34B165A', NULL, NULL, 'Torin''s debug firmware unit', '2012-02-10 23:41:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
