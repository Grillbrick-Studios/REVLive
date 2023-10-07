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
-- Table structure for table : `blockedips`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `blockedips`;
CREATE TABLE `blockedips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '-none-',
  `hitcount` int(11) NOT NULL DEFAULT '0',
  `lasthit` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` tinyint(4) NOT NULL DEFAULT '0',
  `comment` varchar(99) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idxip` (`ipaddress`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;