-- ---------------------------------------------------------
--
-- SIMPLE SQL Dump
--
-- Host Connection Info: Localhost via UNIX socket
-- Generation Time: May 14, 2023 at 17:06 PM ( UTC )
-- Server version: 5.7.42
-- PHP Version: 8.1.18
--
-- ---------------------------------------------------------



SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- ---------------------------------------------------------
--
-- Table structure for table : `users`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `cursession` varchar(50) DEFAULT NULL,
  `superman` tinyint(4) DEFAULT NULL,
  `lastaccessed` datetime DEFAULT NULL,
  `timezone` varchar(40) DEFAULT NULL,
  `sqn` tinyint(4) NOT NULL DEFAULT '0',
  `edit` tinyint(4) NOT NULL DEFAULT '0',
  `appxedit` tinyint(4) NOT NULL DEFAULT '0',
  `resourceedit` tinyint(4) NOT NULL DEFAULT '0',
  `chronedit` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;


--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `username`, `password`, `firstname`, `lastname`, `cursession`, `superman`, `lastaccessed`, `timezone`, `sqn`, `edit`, `appxedit`, `resourceedit`, `chronedit`) VALUES
(1, 'rswoods', '0th3r1', 'Rob', 'Woods', 'FmaV9FtUX3i1h0HfSLyvkSgvnwFLLC', 1, '2022-04-13 01:34:29', 'America/New_York', 98, 1, 1, 1, 1),
(2, 'jschoenheit', 'jenivee', 'John', 'Schoenheit', 'UtAsTTJCy2Yy1ENVfxr5TAIHQUe8T7', 0, '2022-04-13 01:09:20', 'America/New_York', 1, 1, 1, 1, 1),
(5, 'rdugan', 'warmachinerox', 'Renee', 'Dugan', '6pyvY2JxLneSoQwe9yT4QvZSKmsB4m', 0, '2019-02-08 16:04:19', 'America/New_York', 99, 0, 0, 0, 0),
(7, 'jwierwille', 'jwierwi11e', 'Jerry', 'Wierwille', 'UjdEtrwEYJKBCooja8B3HKe8aoLYo7', 1, '2022-04-12 19:31:24', 'America/New_York', 2, 1, 1, 1, 1),
(9, 'cgreen', 'cgr33n', 'Cris', 'Green', '8jM7Xp9LriTQgxr24SlamJDORA5Rvv', 0, '2022-04-13 00:53:51', 'America/New_York', 97, 1, 1, 1, 1),
(10, 'dhickman', 'dh1c3man', 'Dennis', 'Hickman', 'ZtjCWf7CuKocGCviCpAGJKvONGqzX9', 0, '2022-04-05 23:13:48', 'America/New_York', 90, 0, 0, 1, 1),
(11, 'mruth', 'mtruth!', 'Michelle', 'Ruth', 'Uz2MFxUpcDen7j7Ti4HMgxXWPRXs2q', 0, '2019-03-24 14:38:12', 'America/Los_Angeles', 99, 0, 0, 0, 0),
(12, 'cmoschell', 'Iwasborn97', 'Chance', 'Moschell', 'xj9mZY3u5Kzq8AzveMLJM21zd3Y8uS', 0, '2022-03-05 15:31:48', 'America/Chicago', 99, 0, 0, 0, 0),
(13, 'jcutler', 'jcu7l3r', 'James', 'Cutler', '5ElgmuyOynYaaR0dWPZ0V41B4dzE0M', 0, '2021-03-05 18:59:58', 'America/Los_Angeles', 99, 0, 0, 0, 0),
(14, 'scarlson', 'sc@rls0n', 'Sue', 'Carlson', 'IEf78tDIYRNOnFn3eHD0DjmWPgcdwZ', 0, '2021-09-24 16:13:51', 'America/New_York', 99, 0, 0, 0, 0),
(16, 'tscholten', 'REVacc0unt#$', 'Tim', 'Scholten', 'G1Z5V7XmN55cjRz6llG2YphTk10QKY', 0, '2022-03-24 00:03:54', 'America/Chicago', 99, 0, 0, 0, 0),
(17, 'jbarnes', 'jb@rn3s', 'Johnny', 'Barnes', '43U1jzLiF2zbnPKlWS9YGRLKDAo2zL', 0, '2022-04-12 19:46:56', 'America/Chicago', 4, 0, 1, 0, 0),
(18, 'rlauer', 'rl@v3r', 'Rich', 'Lauer', 'FrUFYGYcvFlHzc1N14NOpGFYL3Zu49', 0, '2022-04-13 01:09:45', 'America/New_York', 98, 0, 0, 0, 1);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;