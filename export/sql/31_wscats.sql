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
-- Table structure for table : `wscats`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `wscats`;
CREATE TABLE `wscats` (
  `wscatid` int(11) NOT NULL AUTO_INCREMENT,
  `wscat` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `sqn` smallint(6) NOT NULL,
  PRIMARY KEY (`wscatid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Dumping data for table `wscats`
--

INSERT INTO `wscats` (`wscatid`, `wscat`, `sqn`) VALUES
(1, 'Hebrew', 1),
(2, 'Greek', 2),
(3, 'Figure of Speech', 3),
(4, 'Miscellaneous', 99);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;