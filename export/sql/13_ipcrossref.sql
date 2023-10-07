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
-- Table structure for table : `ipcrossref`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `ipcrossref`;
CREATE TABLE `ipcrossref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(50) CHARACTER SET utf8 NOT NULL,
  `iplocation` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ipaddress` (`ipaddress`)
) ENGINE=InnoDB AUTO_INCREMENT=163278 DEFAULT CHARSET=latin1;


--
-- Dumping data for table `ipcrossref`
--

INSERT INTO `ipcrossref` (`id`, `ipaddress`, `iplocation`) VALUES
(163267, '172.93.60.8', 'Rob'),
(163274, '108.4.135.244', 'Jerry'),
(163275, '102.165.16.141', 'Trenton, NJ, US'),
(163276, '67.241.30.187', 'Clifton Park, NY, US'),
(163277, '137.83.97.27', 'Jim Hessin');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;