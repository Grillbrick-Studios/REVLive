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
-- Table structure for table : `category`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `catid` int(11) NOT NULL AUTO_INCREMENT,
  `cattype` tinyint(4) NOT NULL DEFAULT '0',
  `sqn` smallint(6) NOT NULL DEFAULT '99',
  `categoryname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`catid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Dumping data for table `category`
--

INSERT INTO `category` (`catid`, `cattype`, `sqn`, `categoryname`) VALUES
(1, 0, 99, 'Israel'),
(2, 0, 99, 'Judah'),
(3, 0, 99, 'Jesus Christ'),
(6, 1, 99, 'Biblical Customs & Culture'),
(7, 1, 99, 'World History'),
(8, 1, 99, 'Lexicons & Dictionaries'),
(9, 1, 99, 'Bible Atlases'),
(10, 1, 99, 'Early Church Fathers'),
(11, 1, 99, 'Extra Biblical Writings'),
(12, 1, 99, 'Concordances'),
(13, 1, 99, 'Commentaries'),
(14, 1, 99, 'Archeology & Chronology'),
(15, 1, 99, 'Bible Translations'),
(16, 1, 99, 'Misc. Items of Interest and Relevance'),
(17, 1, 99, 'Books to Purchase'),
(18, 1, 99, 'Helpful Web-Sites and Resources'),
(20, 1, 99, 'Judaism'),
(21, 1, 99, 'PDFs'),
(22, 1, 99, 'Spanish Docs');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;