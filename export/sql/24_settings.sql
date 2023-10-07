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
-- Table structure for table : `settings`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `settingtype` varchar(10) NOT NULL DEFAULT 'static',
  `settingname` varchar(50) NOT NULL,
  `somenumber` decimal(10,2) DEFAULT NULL,
  `somestring` varchar(50) DEFAULT NULL,
  `sometime` datetime DEFAULT NULL,
  `settingdesc` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;


--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `settingtype`, `settingname`, `somenumber`, `somestring`, `sometime`, `settingdesc`) VALUES
(1, 'static', 'exportclean', null, null, '2023-05-14 17:04:00', null),
(2, 'static', 'mappingclean', null, null, '2023-05-11 18:33:00', null),
(3, 'static', 'ipslastzeroed', null, null, '2023-02-17 15:04:20', null),
(4, 'static', 'autoblock', 1.00, null, null, null),
(5, 'switch', 'revch', 1.00, null, null, 'Chronology'),
(6, 'switch', 'revtp', 1.00, null, null, 'Topics'),
(7, 'switch', 'revws', 1.00, null, null, 'Word Studies'),
(11, 'switch', 'viewsrc', 0.00, null, null, 'view source, usually off'),
(12, 'variable', 'fileversion', 1.53, null, null, 'force reload of includes'),
(14, 'variable', 'sopstimeout', 3.00, null, null, 'minutes to autopark'),
(15, 'switch', 'sopsislive', 1.00, null, null, 'SOPS System'),
(17, 'variable', 'sopstimeoutextend', 20.00, null, null, 'sops extend minutes'),
(18, 'switch', 'sopstimeoutding', 1.00, null, null, 'SOPS dings at <20 sec'),
(19, 'switch', 'revblog', 0.00, null, null, 'REV Blog. Need writer..'),
(20, 'static', 'estimestamp', null, '_20230408', null, null);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;