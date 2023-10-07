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
-- Table structure for table : `playlist`
--
-- ---------------------------------------------------------

DROP TABLE IF EXISTS `playlist`;
CREATE TABLE `playlist` (
  `playlistid` int(11) NOT NULL AUTO_INCREMENT,
  `pltypeid` smallint(6) NOT NULL DEFAULT '0',
  `playlisttitle` varchar(200) NOT NULL,
  `sqn` smallint(6) NOT NULL DEFAULT '0',
  `edituserid` smallint(6) NOT NULL DEFAULT '0',
  `thumbnail` varchar(200) DEFAULT NULL,
  `description` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`playlistid`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`playlistid`, `pltypeid`, `playlisttitle`, `sqn`, `edituserid`, `thumbnail`, `description`) VALUES
(9, 3, 'Words of Wisdom', 1, 1, '/i/wow_logo.png', '<p>We are excited to share with you this new multi-part podcast series! The Book of Proverbs is full of such great edification, admonition, and wisdom to help enrich and enlighten the journey of life&mdash;and in our brand-new Podcast &ldquo;WORDS OF WISDOM,&rdquo; we&rsquo;re taking a deeper look at this wonderful part of Scripture!</p>'),
(12, 1, 'The Open View of God', 1, 1, 'https://i.ytimg.com/vi/Of52ji-NfBk/mqdefault.jpg', '<p>This is a 14 part series on the open view of God by John Schoenheit.</p> <p><a href="https://www.youtube.com/playlist?list=PL26wBwzo4McrshkuD4uJxGrE8u6mDELjN" target="_blank">Watch this playlist on Youtube.</a></p> <p>View or download the syllabus for this seminar by clicking <a href="https://www.revisedenglishversion.com/jsondload.php?fil=2017" target="_blank">this link</a>.</p>'),
(13, 5, 'Truth Matters', 2, 1, '/i/seminars/TM.png', '<p>Truth Matters is a series of teachings in which we examine the existence of truth, seek to discover what truth is, and learn what it means to walk in the light of truth.</p> <p>To download this seminar, <a href="https://media.spiritandtruthonline.org/Truth_Matters/Truth_Matters.zip">click here</a>.</p>'),
(14, 3, 'Highlights of Living Out Loud', 4, 0, null, '<p>Highlights of Living Out Loud from 2005 and 2006</p>'),
(15, 3, 'The Role of Women in the Church', 5, 0, null, '<p>John Schoenheit and Sue Carlson teach on &ldquo;The Role of Women in the Church&rdquo;.</p>'),
(17, 5, 'A Journey Through the Old Testament', 1, 1, '/i/seminars/JTOT.jpg', '<p>This 26-hour seminar provides a scope and understanding of the chronology and important events of the Old Testament, which is the foundation for understanding the New Testament. Genesis through Malachi is the same training manual that shaped the life of our Lord Jesus Christ. A rich knowledge of biblical geography, customs, culture, and history is set forth as many biblical characters come to life. The teachings also present practical keys for Christian living today.</p> <p>You can download the seminar if you like. It&rsquo;s large, so there are four files:</p> <p><a href="https://media.spiritandtruthonline.org/OT/OT_Part_1.zip" rel="noopener noreferrer" title="Journey Zip (Part 1)">Click here to download the zip file (part 1)</a><br /> <a href="https://media.spiritandtruthonline.org/OT/OT_Part_2.zip" rel="noopener noreferrer" title="Journey (Part 2)">Click here to download the zip file (part 2)</a><br /> <a href="https://media.spiritandtruthonline.org/OT/OT_Part_3.zip" rel="noopener noreferrer" title="Journey (Part 3)">Click here to download the zip file (part 3)</a><br /> <a href="https://media.spiritandtruthonline.org/OT/OT_Part_4.zip" rel="noopener noreferrer" title="Journey (Part 4)">Click here to download the zip file (part 4)</a></p> <p>To view the PDF syllabus that comes with this audio seminar, <a href="https://spiritandtruthonline.org/wp-content/uploads/2021/05/OT_Syllabus_whole.pdf" rel="noopener noreferrer" target="_blank" title="Journey Syllabus">click here</a>.</p>'),
(18, 5, 'New Life in Christ', 5, 1, '/i/seminars/NLIC.jpg', '<p>This seminar presents the fundamentals of Christian beliefs and behaviors, as well as instruction in how to understand the Bible. &ldquo;New Life in Christ&rdquo; is a springboard for a new Christian to establish a basis in the blessings and responsibilities of being God&rsquo;s child. The seminar closes with a prayer that encapsulates the point of the class, Philippians 1:9: &ldquo;&hellip;that your love may abound more and more in knowledge and depth of insight so that you may be able to discern what is best&hellip;to the glory and praise of God.&rdquo;</p> <p>You may download the entire seminar (note: the file is big!):<br /> <a href="https://media.spiritandtruthonline.org/NLIC/NLIC.zip" rel="noopener noreferrer" title="New Life in Christ Zip">Click here to download the zip file</a>.</p> <p>To view the PDF syllabus that comes with this audio class, <a href="https://spiritandtruthonline.org/wp-content/uploads/2021/05/New-Life-In-Christ-Syllabus.pdf" rel="noopener noreferrer" target="_blank" title="New Life in Christ Syllabus">click here</a>.</p>'),
(19, 5, 'Death and Resurrection to Life', 4, 1, '/i/seminars/DRTL.jpg', '<p>Many Christians believe that when they die, they immediately go to heaven to be with Jesus. This seminar is designed to provide freedom in knowing what truly happens when we die. To understand the true nature of death, we must look to Scripture, not to religious traditions or worldly cultures. Scripture verses taken out of context over the years have led to the erroneous conclusion that the soul lives on after death. This seminar illustrates the biblical truth concerning death and resurrection.</p> <p>To download this seminar, <a href="https://media.spiritandtruthonline.org/Death_Resurrection/Death_and_Resurrection.zip">click here</a>.</p> <p>To view or print the syllabus, <a href="https://spiritandtruthonline.org/wp-content/uploads/2021/05/Death-Resurrection-Syllabus.pdf" rel="noopener noreferrer" target="_blank" title="Syllabus PDF">click here</a>.</p>'),
(20, 5, 'The Creation-Evolution Controversy', 3, 1, '/i/seminars/CEC.jpg', '<p>This 6-hour seminar provides information about the Creation &ndash; Evolution Controversy, showing that evolution, as defined in the seminar, is a religion and not a science. This conclusion is reached by examining a range of scientific perspectives. The arguments for evolution and creation are examined by comparing the beliefs of both models with the data in the world around us. The information presented should put an end to any confusion that a Christian may have about evolution while increasing their trust in the Biblical record of creation.</p> <p><a href="https://media.spiritandtruthonline.org/Creation_Evolution/The_Creation_Evolution_Controversy.zip" title="Creation Evolution Seminar Zip">Click here to download the zip file</a></p> <p>To view or print the syllabus, <a href="https://spiritandtruthonline.org/wp-content/uploads/2021/05/CEC.pdf" rel="noopener noreferrer" target="_blank" title="Syllabus PDF">click here</a>.</p>'),
(21, 5, 'The Book of Revelation', 2, 1, '/i/seminars/REV.jpg', '<p>God, the gracious Father of our Lord Jesus Christ, has given every person the opportunity for everlasting life. This seminar presents a detailed overview of the promises in the Old Testament made to Israel for life in an age to come. It explains what can be anticipated for life and rewards in the Millennial Kingdom based on these Old Testament records. The period of Tribulation is explored with its end giving way to the Millennial Kingdom. The seminar ends explaining the new heaven and earth, the Everlasting Kingdom, the New Jerusalem, and God&rsquo;s dwelling place being forever with man.</p> <p><a href="https://media.spiritandtruthonline.org/Revelation/Revelation%20Seminar.zip" rel="noopener noreferrer" title="Revelation Seminar Zip File">Click here to download the zip file</a></p> <p>To view the PDF syllabus that comes with this audio seminar, <a href="https://spiritandtruthonline.org/wp-content/uploads/2021/05/Revelation_Syllabus.pdf" rel="noopener noreferrer" target="_blank" title="Revelation Seminar Syllabus">click here</a>.</p>'),
(22, 5, 'On the Errors of the Trinity', 6, 1, '/i/seminars/TE.jpg', '<p>Since being officially codified at the Council of Nicaea in 325 AD, the concept of the Trinity has caused endless confusion for many truth-seeking Christians. This seminar is an in-depth study of the doctrine of the Trinity alongside the testimony of Scripture to demonstrate how the Trinity lacks a sound, biblical basis. It is crucial for Christians to understand who Jesus Christ is and who God is, not only so they can make a rational defense of their faith, but also so they can experience a relationship with the one true God and His son Jesus Christ.</p> <p>The files for this seminar can be downloaded here. Note, they are large, and may take a while to download.</p> <p>Download <a href="https://media.spiritandtruthonline.org/On_Errors_Of_Trinity/On_Errors_of_Trinity_Part%201.zip">part 1</a>.<br /> Download <a href="https://media.spiritandtruthonline.org/On_Errors_Of_Trinity/On_Errors_of_Trinity_Part%202.zip">part 2</a>.</p>'),
(23, 1, 'The Consequences of Believing in the Trinity', 2, 1, 'https://i.ytimg.com/vi/w9B8ypkRV-g/mqdefault.jpg', '<p>This is a three part series on the consequences of believing in the Trinity.</p> <p><a href="https://www.youtube.com/playlist?list=PLpG2jsATq0iq5g5fNC225iZMU8Q844oNQ" target="_blank">Watch this series on Youtube</a>.</p>'),
(24, 1, 'What is Jesus Christ doing today?', 3, 1, 'https://i.ytimg.com/vi/044WY1rdSqk/mqdefault.jpg', '<p>This is a six part series by John Schoenheit on &ldquo;What is Jesus Christ Doing Today&rdquo;.</p>'),
(25, 1, 'Don&rsquo;t Blame God', 4, 1, 'https://i.ytimg.com/vi/RDgIRWYzPBc/mqdefault.jpg', '<p>This is a four part series on not blaming God for everything that happens in this world. We are in the middle of a very real spiritual war, with God and good people and angels on one side and the Devil and bad angels and people on the other side.</p>'),
(26, 1, 'The KJV Controversy', 5, 1, 'https://i.ytimg.com/vi/ftdYhrqum8s/mqdefault.jpg', '<p>Some Christians believe the King James Bible is the only English translation of the Bible that is the true Word of God. This four part series explores whether or not that claim is valid.</p>'),
(27, 1, 'Israel Tour 2000', 6, 1, 'https://i.ytimg.com/vi/uSkPKrHfduc/mqdefault.jpg', '<p>Israel Tour 2000 - On Location With John W. Schoenheit. These videos were shot in the year 2000 and the tapes were lost for many years. Now found, we have imported and edited the footage. The video quality is not HD as it was shot in 2000, but the audio and content are great! We hope that they will be a blessing to you and enrich your view of Scripture as you see the Bible Lands.</p>'),
(28, 1, 'Christian Baptism', 0, 1, 'https://i.ytimg.com/vi/rWzAwA58jV8/mqdefault.jpg', '<p>This is a series of teachings by John Schoenheit on Christian Baptism.</p>'),
(29, 1, 'Homosexuality and the Bible', 0, 1, 'https://i.ytimg.com/vi/qm269_-74tM/mqdefault.jpg', '<p>Homosexuality is a hot topic nowadays, especially within Christian circles, and often results in much confusion and hurt. In this video series Dan Gallagher looks at the topic of homosexuality from the biblical perspective of both grace and truth.</p> <p><a href="https://www.youtube.com/playlist?list=PL26wBwzo4McpBpK5-rzAM4elT0Hv2dTDu" target="_blank">Watch this series on Youtube</a>.</p>'),
(30, 1, 'God&rsquo;s Divine Council', 0, 1, 'https://i.ytimg.com/vi/kmeVKcRrGLw/mqdefault.jpg', '<p>In this series John Schoenheit uses Scripture and logic to show that God has a council of spirit being elders with whom He works to administer His creation.</p>'),
(31, 1, 'Lazarus and John 11', 0, 1, 'https://i.ytimg.com/vi/ODn-VDrO6kA/mqdefault.jpg', '<p>In this 3 part series John Schoenheit takes a deeper look at the account of Lazarus.</p>'),
(33, 1, 'The Four Servant Songs of Isaiah', 0, 1, 'https://i.ytimg.com/vi/ehIWQi6hASY/mqdefault.jpg', '<p>John Schoenheit presents a five part teaching on the Servant Songs of Isaiah, four prophecies that laid out what Jesus was going to have to do for us.</p>'),
(34, 1, 'The Feasts of Israel', 0, 1, 'https://i.ytimg.com/vi/pLH3kAghP5A/mqdefault.jpg', '<p>In this series Dan Gallagher gives us a look at the 7 different feasts the Israelites were required to observe.</p> <p>For notes on this series, <a href="https://www.stfonline.org/pdf/feasts-of-israel-notes.pdf" target="_blank">click here</a>.</p>'),
(35, 1, 'Wisdom - The Wise, The Fool, & The Wicked', 0, 1, 'https://i.ytimg.com/vi/GxLlR_x-6TY/mqdefault.jpg', null),
(36, 1, 'The Spiritual Battle', 0, 1, 'https://i.ytimg.com/vi/v_7X4fRneX4/mqdefault.jpg', '<p>In this series Dan Gallagher teaches on the nature of the Devil and how to stand against him.</p>'),
(37, 1, 'The Interpretation of Tongues', 0, 1, 'https://i.ytimg.com/vi/__pMnHvufsc/mqdefault.jpg', '<p>In this video series John Schoenheit goes into detail on what exactly the Interpretation of Tongues really is.</p>'),
(39, 1, 'Love: The more Excellent Way', 0, 1, 'https://i.ytimg.com/vi/b09FJk6PW24/mqdefault.jpg', '<p>Love is one of the greatest topics in the Bible, in fact it could even be said that the Bible is the greatest love story ever told. In this series of teachings Dan Gallagher explores 1 Corinthians 13 and the specific characteristics of love.</p>'),
(40, 1, 'Manners & Customs in the Bible', 0, 1, 'https://i.ytimg.com/vi/3EHkoowC1jk/mqdefault.jpg', '<p>The Bible is an eastern book which means that it is filled with customs and traditions that are unfamiliar to many readers in the western world. In this series of teaches we explore various sections of Scripture where eastern customs are used in order to gain a fuller understanding of the truths being communicated.</p>'),
(41, 1, 'Understanding Curses', 0, 1, 'https://i.ytimg.com/vi/UnPQ9SJ2JdI/mqdefault.jpg', '<p>In comparison to Biblical times, nowadays many people do not realize the spiritual impact of their actions or words. In this series of teachings, Dan Gallagher takes us through the Bible and shows how curses are a mechanism that often times opens the door for demons to gain influence into a person&#39;s life, the lives of their family, their children, and even future generations. Dan provides the listener with a basic understanding on three types of curses: curses resulting from a person&#39;s actions, curses from words, and curses from the sins of their ancestors. Curses are one of the weapons that Satan relies on to interfere with the blessings God has for His people. The information in this teaching is an essential understanding for anyone who desires to wage a successful defense in the spiritual battle.</p>'),
(42, 1, 'Loving Above the World', 0, 1, 'https://i.ytimg.com/vi/thm0QA8u8nE/mqdefault.jpg', '<p>What many people know or think about love is often a result of what they see done in the world. In this series of teachings Dave Hanson helps us learn how to love the way God loves, that is, how to love above the world.</p>'),
(43, 1, 'Speaking in Tongues', 0, 1, 'https://i.ytimg.com/vi/lEJCpOEuw-g/mqdefault.jpg', '<p>This is a four part series by John Schoenheit on the manifestation of speaking in tongues.</p> <p>1) What it is not.<br /> 2) What it is.<br /> 3) What it is for.<br /> 4) How to do it.</p>');
INSERT INTO `playlist` (`playlistid`, `pltypeid`, `playlisttitle`, `sqn`, `edituserid`, `thumbnail`, `description`) VALUES
(44, 1, 'Salvation is Permanent for Christians', 0, 1, 'https://i.ytimg.com/vi/RYbGmhNu6F4/mqdefault.jpg', '<p>John W. Schoenheit of Spirit &amp; Truth Fellowship International teaches a five part series on the permanence of Christian salvation in this Administration of God&rsquo;s Grace which started on the Day of Pentecost. Now, we are saved by grace, a gift from God - no longer from our works (Eph. 2:8).</p>'),
(45, 1, 'Historically Verifiable Prophecies', 0, 1, 'https://i.ytimg.com/vi/PdUAtcRTHA4/mqdefault.jpg', '<p>Can we trust the Bible? This is a series of teachings that demonstrates the Bible can be trusted by providing proof of historically verifiable prophecies mentioned in the Bible.</p>'),
(46, 1, 'Failing Forward: Turning Mistakes into Success', 0, 1, 'https://i.ytimg.com/vi/3_yO7ELg1z8/mqdefault.jpg', '<p>How do you relate to the mistakes or failures in your life? This is a four part series of teachings that can change your life by changing the way you relate to decision making and turning mistakes into stepping stones for success.</p>'),
(47, 1, 'The Life of Moses: Learning from his Example', 0, 1, 'https://i.ytimg.com/vi/nvjbhfTDlMQ/mqdefault.jpg', '<p>John W. Schoenheit of Spirit &amp; Truth Fellowship International teaches a three part series on the life of Moses. There is a lot we can learn from how God worked with him.</p>'),
(48, 1, 'The Discipline of Forgiveness', 0, 1, 'https://i.ytimg.com/vi/C_DFsTNk7b0/mqdefault.jpg', '<p>Dan Gallagher of Spirit &amp; Truth Fellowship International teaches a four part series on The Discipline of Forgiveness.</p>'),
(49, 1, 'Truth Matters', 0, 1, 'https://i.ytimg.com/vi/YBePVRWwTIk/mqdefault.jpg', '<p>Dan Gallagher of Spirit &amp; Truth Fellowship International teaches a six part series on the subject of Truth Matters.</p>'),
(50, 1, 'Prophetic Ministry', 0, 9, 'https://i.ytimg.com/vi/xbNpjDrJgQw/mqdefault.jpg', '<p>The Prophetic ministry is a wonderful blessing to God&#39;s people, and is a vital part of the Body of Christ, operating as its eyes and ears. The following series of teachings are intended to help God&#39;s people understand the role and ministry of a prophet, and the differences between New and Old Testament prophetic ministries, what makes a true or false prophet, and keys to walking in the Prophetic ministry.</p>'),
(51, 1, 'The Day of Pentecost', 0, 1, 'https://i.ytimg.com/vi/1jJUwL6ntnY/mqdefault.jpg', '<p>John W. Schoenheit of Spirit &amp; Truth Fellowship International teaches a three part series on the history and significance of the Day of Pentecost, also known as the Feast of Weeks.</p>'),
(52, 1, 'Love | What is Love?', 0, 1, 'https://i.ytimg.com/vi/maoQpWJt9VQ/mqdefault.jpg', '<p>John W. Schoenheit of Spirit &amp; Truth Fellowship International [www.STFonline.org] teaches a Bible study series on Love.</p>'),
(53, 1, 'The Book of Ruth', 0, 1, 'https://i.ytimg.com/vi/I5f5wRrpoUc/mqdefault.jpg', '<p>This is a multi-part exposition of the Book of Ruth by John Schoenheit</p>'),
(54, 1, '1 Thessalonians Series', 0, 1, 'https://i.ytimg.com/vi/g-jBSaKD_EA/mqdefault.jpg', '<p>This playlist contains the teachings from the Thursday Night Virtual Fellowships from Fall of 2000 on Paul&rsquo;s first epistle to the Thessalonians.</p>'),
(55, 1, 'Tattoos: What Does the Bible Really Say About Them?', 0, 1, 'https://i.ytimg.com/vi/c0O6t8wEoM0/mqdefault.jpg', '<p>Dan Gallagher of Spirit &amp; Truth Fellowship International teaches a five-part series on the subject of Tattoos.</p>'),
(56, 1, 'A Message of Knowledge or Wisdom', 0, 1, 'https://i.ytimg.com/vi/rXRjWw6uOkQ/mqdefault.jpg', '<p>The gift of holy spirit is given to every one who is born of God. This spiritual gift enables all who receive it to be operated (manifested in nine ways). In these teachings we explore the manifestations of a Message of Knowledge and a Message of Wisdom, what they are and what they are not.</p>'),
(57, 1, 'Verses Used to Support the Trinity', 0, 9, 'https://i0.wp.com/www.biblicalunitarian.com/wp-content/uploads/2018/02/john-1-5-commentary.png?resize=1080%2C675&ssl=1', '<p>Dedicated to helping you understand the verses that people try to use to support the doctrine of the Trinity.</p>'),
(58, 1, 'Verses that Support the Biblical Unitarian Position', 0, 1, 'https://i.ytimg.com/vi/TFQ8ehq-erU/mqdefault.jpg', '<p>This playlist contains three videos that present verses that support Biblical Unitarianism.</p> <p><a href="https://www.youtube.com/playlist?list=PLpG2jsATq0ipu-CAsK8hR0nG0S1wfpu3P" target="_blank">Watch this playlist on Youtube</a></p>'),
(59, 1, 'Can a Christian be Possessed?', 0, 1, 'https://i.ytimg.com/vi/5V471obb27c/mqdefault.jpg', '<p>Historically there has been confusion regarding whether a Christian can be possessed by a demon or not. One of the reasons for Satan&#39;s success in undermining a Christians effectiveness in the spiritual battle is a result of our ignorance of spiritual matters such as this. In these teachings Dan Gallagher examines provides the solid biblical basis for understanding the nature of demonization, what it is, and how to effectively guard against it and evict demonic intruders.</p>'),
(60, 1, 'Joy - What is True Joy?', 0, 9, 'https://i.ytimg.com/vi/M1JbUhCGBIQ/hqdefault.jpg?sqp=-oaymwEXCNACELwBSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLCYUJ8LwFRy8Zbv2kdk1hSd-6D7hg', '<p>What is joy and how can we have joy in our lives? Dan Gallagher teaches this six-part series, explaining the many ways that God brings joy to dwell in us, the difference between joy and happiness, and things can can block us from experiencing joy.</p>'),
(61, 4, 'Bible Manners and Customs', 0, 9, 'https://www.truthortradition.com/wp-content/uploads/2013/08/introduction_bible_manners_customs-300x142.jpg', '<p>This is a collection of articles taken from the book <a href="http://stfonlinestore.com/bmc.aspx" rel="noopener noreferrer" target="_blank">Bible Manners &amp; Customs</a> by Rev. G.M. Mackie, M.A, 1898 (which we have revised and reprinted).</p>'),
(62, 1, '2 Thessalonian Series', 0, 1, 'https://i.ytimg.com/vi/HoxDvaWLYN4/mqdefault.jpg', '<p>This playlist contains the teachings from the Thursday Night Virtual Fellowships from Winter-Spring 2021 on Paul&rsquo;s second epistle to the Thessalonians.</p>'),
(63, 1, 'What Trust Looks Like', 0, 1, 'https://i.ytimg.com/vi/yXj94PIZML4/mqdefault.jpg', '<p>This is a four part series by Ellen Cutler on what trust looks like.</p>'),
(64, 1, 'The Times of Restitution of All Things', 0, 1, 'https://i.ytimg.com/vi/73qLgqDzBvw/mqdefault.jpg', '<p>This is a four part series on the End Times by Rhett Major.</p>'),
(65, 1, 'Sharing Your Faith Like Jesus', 0, 1, '/i/thumbnails/womanatthewell.jpg', '<p>This is a three part series by Jeff Tyler on the record of Jesus and the Samaritan woman at the well.</p>'),
(66, 1, 'Following Jesus Together', 0, 1, null, '<p>This is a series of teachings by Jeff Tyler on following Jesus together.</p>'),
(67, 7, 'The REV Appendices in Spanish', 0, 1, null, '<p>Thanks to the hard work of Olga and Cris Green, the REV appendices are available to download in Spanish</p>');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;