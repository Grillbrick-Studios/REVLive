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
-- Table structure for table : `logexcludeips`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `logexcludeips`;
CREATE TABLE `logexcludeips` (
  `ipid` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(20) NOT NULL,
  `ipcomment` varchar(99) DEFAULT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `lastview` datetime DEFAULT NULL,
  PRIMARY KEY (`ipid`)
) ENGINE=InnoDB AUTO_INCREMENT=550 DEFAULT CHARSET=latin1;


--
-- Dumping data for table `logexcludeips`
--

INSERT INTO `logexcludeips` (`ipid`, `ipaddress`, `ipcomment`, `hits`, `lastview`) VALUES
(547, '0.0.0.0', '!zPlaceholder for deleted logs', 3, '2023-05-01 16:43:30'),
(548, '0.0.0.1', '!zzBingbots (see fn.php 44, 504)', 0, '2023-05-01 16:43:18'),
(549, '0.0.0.2', '!zzGoogbots (see fn.php 45, 505)', 0, '2023-05-01 17:01:40');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;