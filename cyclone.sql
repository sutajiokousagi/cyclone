-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 29, 2012 at 02:46 AM
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
(2, 1, 10, 'send email', 'email', 'to|subject|message', 'Send an email to someone with a subject & a message'),
(3, 1, 2, 'post a Tweet with', 'tweet', 'text', 'Post a tweet'),
(4, 1, 3, 'update my Facebook status with', 'update_status', 'message|link', 'Update your Facebook status'),
(5, 1, 12, 'switch ON digital output', 'digital_out_on', 'channel', 'Switch on a digital output channel'),
(6, 1, 12, 'switch OFF digital output', 'digital_out_off', 'channel', 'Switch off a digital output channel'),
(7, 1, 12, 'display text on LCD', 'lcd_text', 'text', 'Show a simple text on LCD'),
(8, 1, 12, 'stop motor', 'motor_stop', 'channel', 'Stop a motor channel'),
(9, 1, 12, 'move motor forward', 'motor_forward', 'channel|speed', 'Set a motor channel to move forward with a given speed'),
(10, 1, 12, 'move motor backward', 'motor_backward', 'channel|speed', 'Set a motor channel to move backward with a given speed');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
(4, 0, 'RSS', 'rss', 0, 0, 'A module that generates triggers based on content changes of an RSS feed'),
(5, 1, 'HDMI', 'hdmi', 1, 0, 'A module that generates triggers based on HDMI hardware events'),
(6, 1, 'Network', 'network', 1, 0, 'A module that generates triggers based on network hardware events'),
(7, 1, 'GMail', 'gmail', 2, 0, 'A module that generates triggers based on user''s GMail events'),
(8, 1, 'HelloTrigger', 'hellotrigger', 0, 0, 'A simple HelloWorld module with sample triggers'),
(9, 1, 'HelloFilter', 'hellofilter', 0, 1, 'A simple HelloFilter module with basic string, number & datetime processing'),
(10, 1, 'HelloAction', 'helloaction', 0, 2, 'A simple HelloAction module with basic sample actions'),
(11, 0, 'iOS', 'ios', 0, 0, 'A module that generates triggers based on iOS device event'),
(12, 1, 'NeTV', 'netv', 1, 3, 'A hardware module for NeTV motor controller board');

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
(83, 0, 'system_ext_callbackUrl_facebook', 'http://cyclone.torinnguyen.com/ext_callback_facebook.php', NULL, '2012-05-12 00:00:00'),
(69, 0, 'system_ext_callbackUrl_twitter', 'http://cyclone.torinnguyen.com/ext_callback_twitter.php', NULL, '2012-04-26 00:00:00'),
(70, 0, 'system_ext_consumerKey_twitter', 'Ab7Iba57LHmhNJAO9BWstg', NULL, '2012-04-26 00:00:00'),
(71, 0, 'system_ext_consumerSecret_twitter', 'd4OuPNkJHHLMKcMzPhtf9MBSbdo9EG6jPeclYbvrg', NULL, '2012-04-26 00:00:00'),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

--
-- Dumping data for table `rules`
--

INSERT INTO `rules` (`rule_id`, `trigger_id`, `trigger_param`, `user_id`, `filter_id`, `filter_param`, `action_id`, `action_param`, `rule_description`, `rule_enabled`, `last_updated`) VALUES
(2, 17, NULL, 1, 0, NULL, 1, '{"text":"This is just a test of simple echo action on even minutes."}', 'Test simple ''echo'' action on even minutes', 0, '2012-02-16 00:31:00'),
(3, 18, NULL, 1, 0, NULL, 1, '{"text":"This is just a test of simple echo action on odd minutes."}', 'Test simple ''echo'' action on odd minutes', 0, '2012-02-16 00:31:00'),
(14, 21, '{"datetime":"2012-04-06 02:47"}	', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Test one-shot trigger","message":"hello!"}', 'Test email module once', 1, '2012-03-26 11:09:39'),
(15, 20, '{"days":"mon,tue,wed,thu,fri","time":"05:10"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Testing weekly trigger","message":"Testing"}', 'Testing weekdays email', 0, '2012-03-21 23:51:52'),
(16, 19, '{"time":"05:15"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Testing daily trigger","message":"Testing "}', 'Testing daily email', 0, '2012-03-21 23:51:52'),
(19, 21, '{"datetime":"2012-04-06 04:55"}', 1, 0, NULL, 3, '{"text":"Auto-tweet with Cyclone  (http://cyclone.torinnguyen.com/ui_rules.php) at 4:55am when I clearly should be sleeping :)"}', 'Test Tweeter module once', 0, '2012-04-06 02:06:28'),
(28, 26, '{"url":"http://www.engadget.com/rss.xml"}', 1, 0, NULL, 2, '{"to":"torinnguyen@gmail.com","subject":"Engadget RSS feed has [count] new article(s)","message":"Engadget RSS feed [url] <br>has [count] new article(s). <br/>Titles: [titles]"}', 'Inform me by email when Engadget has new article', 0, '2012-04-09 02:49:46'),
(23, 23, '{"distance":"300","longitude":"103.8455021","latitude":"1.3417876"}', 1, 0, NULL, 3, '{"text":"Auto-tweet by Cyclone. Reaching home...[distance]m"}', 'Send a tweet when I''m reaching home', 1, '2012-04-08 22:10:57'),
(24, 23, '{"distance":"200","longitude":"103.844193","latitude":"1.277278"}', 1, 0, NULL, 3, '{"text":"Auto-tweet by Cyclone. Reaching office...[distance]m"}', 'Send a tweet when I''m reaching my office', 1, '2012-04-08 22:23:12'),
(33, 14, '{"screen_name":"ntluan"}', 1, 0, NULL, 3, '{"text":"Tweet-shadowing @[username] [tweet1]"}', 'Monitor someone''s tweet and retweet the same thing, with mention his/her screen name', 0, '2012-05-01 01:19:43'),
(34, 15, NULL, 1, 0, NULL, 3, '{"text":"Yay! New follower. Hi there  @[follower1]"}', 'Monitor my followers list and tweet a greeting at him/her', 0, '2012-05-01 01:19:43'),
(42, 21, '{"datetime":"2012-05-15 01:25"}', 1, 0, NULL, 4, '{"message":"testing","link":"http://www.google.com"}', 'Test Facebook module''s update status action once', 1, '2012-05-15 01:10:07'),
(43, 1, NULL, 1, 0, NULL, 4, '{"message":"Testing: HDMI cable is connected","link":"http://cyclone.torinnguyen.com"}', 'Test Facebook module''s update status on HDMI module trigger', 1, '2012-05-16 01:51:34'),
(44, 17, NULL, 1, 0, NULL, 5, '{"channel":"0"}', 'Test NeTV digital output on even minutes', 1, '2012-05-27 01:50:45'),
(45, 18, NULL, 1, 0, NULL, 6, '{"channel":"0"}', 'Test NeTV digital output on odd minutes', 1, '2012-05-27 01:51:34'),
(46, 17, NULL, 1, 0, NULL, 9, '{"channel":"3","speed":"50"}', 'Test NeTV motor control on even minutes', 1, '2012-05-27 01:50:45'),
(47, 18, NULL, 1, 0, NULL, 8, '{"channel":"3"}', 'Test NeTV motor control on odd minutes', 1, '2012-05-27 01:51:34'),
(48, 27, '{"channel":"1"}', 1, 0, NULL, 5, '{"channel":"3"}', NULL, 1, '2012-05-29 00:28:59'),
(49, 28, '{"channel":"1"}', 1, 0, NULL, 6, '{"channel":"3"}', NULL, 1, '2012-05-29 00:29:15'),
(50, 30, '{"channel":"4"}', 1, 0, NULL, 5, '{"channel":"2"}', NULL, 1, '2012-05-29 00:36:15');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `triggers`
--

INSERT INTO `triggers` (`trigger_id`, `trigger_enabled`, `module_id`, `trigger_name`, `trigger_alias`, `trigger_params`, `trigger_description`) VALUES
(1, 1, 5, 'connect', 'connect', NULL, 'Triggered when HDMI cable is plugged in'),
(2, 1, 5, 'disconnect', 'disconnect', NULL, 'Triggered when HDMI cable is unplugged'),
(3, 1, 5, 'resolution change', 'resolution_change', NULL, 'Triggered when HDMI input signal changes resolution'),
(4, 1, 6, 'connect', 'connect', NULL, 'Triggered when network got connected'),
(5, 1, 6, 'disconnect', 'disconnect', NULL, 'Triggered when network is disconnected'),
(6, 1, 6, 'change ip', 'change ip', NULL, 'Triggered when IP Address changes'),
(7, 1, 7, 'new email', 'new email', 'from', 'Triggered when a new email is received'),
(8, 1, 3, 'got tagged', 'got_tagged', NULL, 'Triggered when someone tagged out in a photo'),
(9, 1, 3, 'status change', 'status_change', NULL, 'Triggered when your status changes'),
(10, 1, 3, 'new post', 'new_post', NULL, 'Triggered when you post a new link post'),
(11, 1, 3, 'upload new photo', 'upload_new_photo', NULL, 'Triggered when you upload a new photo'),
(12, 1, 3, 'profile changed', 'profile_change', NULL, 'Triggered when your profile changes'),
(13, 1, 2, 'new tweet from myself', 'new tweet from you', NULL, 'Triggered when you post a new tweet'),
(14, 1, 2, 'new tweet from someone with', 'new tweet from someone', 'screen_name', 'Triggered when a specific person post a new tweet'),
(15, 1, 2, 'new follower', 'new follower', NULL, 'Triggered when you have a new follower'),
(16, 1, 2, 'mentioned', 'mentioned', NULL, 'Triggered when you are mentioned in a tweet'),
(17, 1, 8, 'time is on even minute', 'even_minute', NULL, 'Triggered when the time is on even minutes'),
(18, 1, 8, 'time is on odd minute', 'odd_minute', NULL, 'Triggered when the time is on odd minutes'),
(19, 1, 1, 'daily at', 'daily', 'time', 'Triggered daily at a specific time'),
(20, 1, 1, 'weekly on', 'weekly', 'days|time', 'Triggered weekly on a specific day and time'),
(21, 1, 1, 'once at', 'once', 'datetime', 'Triggered once on a specific date and time'),
(22, 1, 11, 'stays within location', 'within_location', 'distance|longitude|latitude', 'Triggered as long as device''s location is within range'),
(23, 1, 11, 'enter location', 'enter_location', 'distance|longitude|latitude', 'Triggered when device''s location just enters the given range'),
(24, 1, 11, 'leave location', 'leave_location', 'distance|longitude|latitude', 'Triggered when device''s location just leaves the given range'),
(25, 1, 11, 'stays within a location for xxx minutes', 'within_location_minute', 'distance|longitude|latitude|minutes', 'Triggered as long as device''s location is within range for longer than given time'),
(26, 1, 4, 'new RSS entry from', 'rss_new', 'url', 'Triggered when there is a new entry in RSS feed'),
(27, 1, 12, 'digital input is switched ON on', 'digital_input_on', 'channel', 'Triggered when a digital input channel is switched on'),
(28, 1, 12, 'digital input is switched OFF on', 'digital_input_off', 'channel', 'Triggered when a digital input channel is switched on'),
(29, 1, 12, 'digital input changed on', 'digital_input_change', 'channel', 'Triggered when digital input changed'),
(30, 1, 12, 'analog input changed on', 'analog_input_change', 'channel', 'Triggered when analog input changed');

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
