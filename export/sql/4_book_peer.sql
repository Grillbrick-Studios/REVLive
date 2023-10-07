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
-- Table structure for table : `book_peer`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `book_peer`;
CREATE TABLE `book_peer` (
  `testament` smallint(6) NOT NULL,
  `book` smallint(6) NOT NULL,
  `userid` smallint(6) NOT NULL,
  PRIMARY KEY (`testament`,`book`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Dumping data for table `book_peer`
--

INSERT INTO `book_peer` (`testament`, `book`, `userid`) VALUES
(0, 39, 1),
(0, 39, 7),
(0, 39, 9),
(1, 40, 7),
(1, 40, 20),
(1, 44, 1),
(1, 45, 1),
(1, 45, 7),
(1, 45, 9);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;