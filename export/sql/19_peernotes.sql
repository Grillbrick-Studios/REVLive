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
-- Table structure for table : `peernotes`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `peernotes`;
CREATE TABLE `peernotes` (
  `testament` tinyint(4) NOT NULL,
  `book` smallint(6) NOT NULL,
  `chapter` smallint(6) NOT NULL,
  `verse` smallint(6) NOT NULL,
  `author` smallint(6) NOT NULL,
  `lastauthor` smallint(6) NOT NULL,
  `resolved` tinyint(4) NOT NULL DEFAULT '0',
  `colorindex` tinyint(4) NOT NULL DEFAULT '0',
  `notedate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastupdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editnote` varchar(700) COLLATE utf8_unicode_ci DEFAULT NULL,
  `editdetails` mediumtext COLLATE utf8_unicode_ci,
  UNIQUE KEY `idx_editnote` (`testament`,`book`,`chapter`,`verse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Dumping data for table `peernotes`
--

INSERT INTO `peernotes` (`testament`, `book`, `chapter`, `verse`, `author`, `lastauthor`, `resolved`, `colorindex`, `notedate`, `lastupdate`, `editnote`, `editdetails`) VALUES
(1, 40, 6, 4, 20, 20, 0, 0, '2023-02-22 18:43:41', '2023-02-22 18:43:41', 'And here is my reviewer note on the verse.', null),
(1, 44, 2, 6, 1, 1, 0, 1, '2023-02-17 22:59:29', '2023-02-17 22:59:29', 'peernote margin note', '<p style="margin:6px 0 2px 2px;line-height:1em;"><small>Initiated by Rob W on 2/17/23 at 5:59 pm EST</small></p><div style="line-height:1.2em;margin:0 0 0 21px;color:#333;background-color:#fee;"><p style="margin:0 0 7px 2px;">This is the peernote body</p></div>'),
(1, 45, 5, 9, 1, 7, 1, 0, '2023-02-07 02:49:12', '2023-02-07 20:52:13', 'Still thinking about the &ldquo;we can&rdquo; becoming &ldquo;are&rdquo;? Same in v10.', '<div style="display:inline-block;color:#333;background-color:#fee;margin:2px;padding:0 5px;line-height:1.5em;border:1px solid red;border-radius:4px;font-size:80%;">Marked as resolved by Jerry on 2/7/23 at 3:52 pm EST</div><br />');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;