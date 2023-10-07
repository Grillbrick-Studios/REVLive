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
-- Table structure for table : `category_assoc`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `category_assoc`;
CREATE TABLE `category_assoc` (
  `catid` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  UNIQUE KEY `idx_catevent` (`catid`,`eventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Dumping data for table `category_assoc`
--

INSERT INTO `category_assoc` (`catid`, `eventid`) VALUES
(3, 7),
(3, 8),
(3, 15),
(3, 51),
(6, 1822),
(9, 1822),
(16, 2067),
(16, 2320),
(20, 1825),
(21, 2017),
(22, 2039),
(22, 2063),
(22, 2077),
(22, 2088),
(22, 2159),
(22, 2176),
(22, 2237);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;