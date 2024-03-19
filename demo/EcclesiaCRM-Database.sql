-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le :  lun. 11 fév. 2019 à 20:05
-- Version du serveur :  10.2.21-MariaDB
-- Version de PHP :  7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `philippelo_demo_ecrm`
--

-- --------------------------------------------------------

--
-- Structure de la table `addressbookchanges`
--

CREATE TABLE `addressbookchanges` (
  `id` int(11) UNSIGNED NOT NULL,
  `uri` varbinary(200) NOT NULL,
  `synctoken` int(11) UNSIGNED NOT NULL,
  `addressbookid` int(11) UNSIGNED NOT NULL,
  `operation` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `addressbookchanges`
--

INSERT INTO `addressbookchanges` (`id`, `uri`, `synctoken`, `addressbookid`, `operation`) VALUES
(1, 0x555549442d38323963326338372d643666632d343036642d393739312d303635366664333731313465, 1, 1, 1),
(2, 0x555549442d32663232316164382d376533642d346165342d386436372d313661633132393033336338, 2, 1, 1),
(3, 0x555549442d61666431666436392d383866362d343430662d383562622d326466393239636463323939, 3, 1, 1),
(4, 0x555549442d62646665393138382d626134332d343631302d613734332d343539323665303436633231, 1, 4, 1),
(5, 0x555549442d65343337613736662d383734372d346163642d383533362d663532313464393238636437, 1, 5, 1),
(6, 0x555549442d62633562373361622d313065302d346339662d623062362d636434386461393932623330, 2, 5, 1),
(7, 0x35, 2, 4, 3);

-- --------------------------------------------------------

--
-- Structure de la table `addressbooks`
--

CREATE TABLE `addressbooks` (
  `id` int(11) UNSIGNED NOT NULL,
  `principaluri` varbinary(255) DEFAULT NULL,
  `displayname` varchar(255) DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `synctoken` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `groupId` mediumint(8) NOT NULL DEFAULT -1 COMMENT '-1 personal addressbook, >1 for a group in the CRM'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `addressbooks`
--

INSERT INTO `addressbooks` (`id`, `principaluri`, `displayname`, `uri`, `description`, `synctoken`, `groupId`) VALUES
(1, 0x7072696e636970616c732f61646d696e, '0 -3 ans', 0x39313234316130302d373035332d346564632d396565632d393137313863623731353831, 'AddressBook description', 4, 2),
(2, 0x7072696e636970616c732f61646d696e, 'Groupe de maison Strasbourg centre', 0x32303761373531662d323536332d346439342d623639632d653263353565666534333762, 'AddressBook description', 1, 3),
(3, 0x7072696e636970616c732f61646d696e, 'Culte', 0x61323433333132352d363262332d346138332d396566652d376432366437613530613763, 'AddressBook description', 1, 4),
(4, 0x7072696e636970616c732f61646d696e, 'Conseil spirituel', 0x33343533306134632d646131302d343631392d613130322d613731633533666138336336, 'AddressBook description', 3, 7),
(5, 0x7072696e636970616c732f61646d696e, 'class test', 0x35643930316539302d316338372d343764662d616265612d613662343763396138393937, 'AddressBook description', 3, 8),
(6, 0x7072696e636970616c732f61646d696e, 'From EcclesiaCRM3 without Group', 0x36363162366366372d613362632d343161642d393233382d313061646366656134646633, 'AddressBook description', 1, 9),
(7, 0x7072696e636970616c732f61646d696e, 'test', 0x63323839383365382d386261662d343934622d626135652d613437386531613732356166, 'AddressBook description', 1, 10);

-- --------------------------------------------------------

--
-- Structure de la table `addressbookshare`
--

CREATE TABLE `addressbookshare` (
  `id` int(11) UNSIGNED NOT NULL,
  `addressbooksid` int(11) UNSIGNED NOT NULL,
  `principaluri` varbinary(255) DEFAULT NULL,
  `displayname` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `href` varbinary(100) DEFAULT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = owner, 2 = read, 3 = readwrite'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `autopayment_aut`
--

CREATE TABLE `autopayment_aut` (
  `aut_ID` mediumint(9) UNSIGNED NOT NULL,
  `aut_FamID` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `aut_EnableBankDraft` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `aut_EnableCreditCard` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `aut_NextPayDate` date DEFAULT NULL,
  `aut_FYID` mediumint(9) NOT NULL DEFAULT 9,
  `aut_Amount` decimal(6,2) NOT NULL DEFAULT 0.00,
  `aut_Interval` tinyint(3) NOT NULL DEFAULT 1,
  `aut_Fund` tinyint(3) DEFAULT NULL,
  `aut_FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Zip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Phone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_CreditCard` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_ExpMonth` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_ExpYear` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_BankName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Route` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_Account` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_DateLastEdited` datetime DEFAULT NULL,
  `aut_EditedBy` mediumint(9) UNSIGNED DEFAULT NULL,
  `aut_Serial` mediumint(9) NOT NULL DEFAULT 1,
  `aut_CreditCardVanco` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aut_AccountVanco` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `autopayment_aut`
--

INSERT INTO `autopayment_aut` (`aut_ID`, `aut_FamID`, `aut_EnableBankDraft`, `aut_EnableCreditCard`, `aut_NextPayDate`, `aut_FYID`, `aut_Amount`, `aut_Interval`, `aut_Fund`, `aut_FirstName`, `aut_LastName`, `aut_Address1`, `aut_Address2`, `aut_City`, `aut_State`, `aut_Zip`, `aut_Country`, `aut_Phone`, `aut_Email`, `aut_CreditCard`, `aut_ExpMonth`, `aut_ExpYear`, `aut_BankName`, `aut_Route`, `aut_Account`, `aut_DateLastEdited`, `aut_EditedBy`, `aut_Serial`, `aut_CreditCardVanco`, `aut_AccountVanco`) VALUES
(1, 1, 0, 1, '2018-02-04', 22, '120.00', 1, 1, '', '', '12 avenue de Europe', '', 'Strasbourg', '', '67000', 'France', '', '', '1234567', '', '', '', '', '', '2018-02-04 22:22:15', 1, 1, NULL, NULL),
(2, 1, 0, 0, '2018-03-02', 22, '0.00', 1, 1, '', '', '12 avenue de Europe', '', 'Strasbourg', '', '67000', 'France', '', '', '', '', '', '', '', '', '2018-03-02 16:21:30', NULL, 1, NULL, NULL),
(3, 5, 0, 0, '2018-04-02', 22, '0.00', 1, 1, '', '', '1 rue Tartanpoin', '', '', '', '', 'France', '', '', '', '', '', '', '', '', '2018-04-02 22:59:36', NULL, 1, NULL, NULL),
(4, 5, 0, 0, '2018-04-02', 22, '0.00', 1, 1, '', '', '1 rue Tartanpoin', '', '', '', '', 'France', '', '', '', '', '', '', '', '', '2018-04-02 23:01:01', NULL, 1, NULL, NULL),
(5, 4, 0, 1, '2018-04-03', 22, '0.00', 1, 1, '', '', '1 rue des tartares', '', 'Paris', '', '75000', 'France', '', '', '', '', '', '', '', '', '2018-04-03 12:21:26', NULL, 1, NULL, NULL),
(6, 3, 0, 0, '2018-08-16', 22, '0.00', 1, 1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2018-08-16 12:32:56', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `calendarchanges`
--

CREATE TABLE `calendarchanges` (
  `id` int(11) UNSIGNED NOT NULL,
  `uri` varbinary(200) NOT NULL,
  `synctoken` int(11) UNSIGNED NOT NULL,
  `calendarid` int(11) UNSIGNED NOT NULL,
  `operation` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `calendarchanges`
--

INSERT INTO `calendarchanges` (`id`, `uri`, `synctoken`, `calendarid`, `operation`) VALUES
(1, '', 1, 1, 2),
(2, 0x33636436363964342d366664312d346464392d616165372d633861666639613566376161, 1, 2, 1),
(3, 0x61366435366161332d306136622d343462632d396337622d616330396666646331396166, 2, 2, 1),
(4, 0x36633736356161312d653436382d343261322d613738342d616538343430353731333334, 3, 2, 1),
(5, 0x65313365626135622d313836342d343433362d393138622d383264396364303936643466, 4, 2, 1),
(6, 0x39323361366564392d356134322d346166352d386637652d336334653063303239663231, 1, 3, 1),
(7, 0x64366464396366382d636437332d343661312d626130372d353232333938663635303163, 1, 4, 1),
(8, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, 2, 4, 1),
(9, 0x30303835323066342d383138622d346533652d396464382d333930323663643366333830, 3, 4, 1),
(10, '', 5, 2, 2),
(11, '', 1, 6, 2),
(12, '', 1, 5, 2),
(13, '', 4, 4, 2),
(14, '', 2, 1, 2),
(15, '', 2, 3, 2),
(16, '', 1, 7, 2),
(17, 0x41463237454230412d304144452d343042322d394638372d353544363037443338333242, 2, 7, 1),
(18, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, 5, 4, 2),
(19, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, 6, 4, 2),
(20, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, 7, 4, 2),
(21, '', 6, 2, 2),
(22, '', 3, 7, 2),
(23, '', 4, 7, 2),
(24, '', 2, 6, 2),
(25, '', 3, 6, 2),
(26, 0x44334441374243322d323436352d343930432d393945342d383931314246423933334139, 4, 6, 1),
(27, 0x44334441374243322d323436352d343930432d393945342d383931314246423933334139, 5, 6, 2),
(28, 0x39304233303930312d333942452d344243342d413445322d363044453842443932463430, 2, 5, 1),
(29, '', 5, 7, 2),
(30, '', 6, 6, 2),
(31, 0x39304233303930312d333942452d344243342d413445322d363044453842443932463430, 3, 5, 2),
(32, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, 7, 6, 1),
(33, '', 4, 5, 2),
(39, '', 6, 7, 2),
(40, '', 7, 7, 2),
(41, '', 8, 6, 2),
(42, '', 9, 6, 2),
(43, '', 10, 6, 2),
(44, '', 11, 6, 2),
(45, 0x39304233303930312d333942452d344243342d413445322d363044453842443932463430, 5, 5, 2),
(46, 0x39304233303930312d333942452d344243342d413445322d363044453842443932463430, 6, 5, 3),
(47, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 12, 6, 1),
(48, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 13, 6, 2),
(49, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 14, 6, 2),
(50, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 15, 6, 2),
(51, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 16, 6, 2),
(52, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 17, 6, 2),
(53, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 7, 2, 1),
(54, '', 8, 2, 2),
(55, '', 9, 2, 2),
(56, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 10, 2, 2),
(57, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, 18, 6, 2),
(58, '', 8, 7, 2),
(59, '', 9, 7, 2),
(60, '', 10, 7, 2),
(63, 0x43373445333837302d464433342d343535392d414142442d323734384137323630343946, 11, 7, 1),
(64, '', 12, 7, 2),
(65, '', 13, 7, 2),
(66, '', 14, 7, 2),
(67, '', 15, 7, 2),
(68, '', 16, 7, 2),
(72, 0x41463237454230412d304144452d343042322d394638372d353544363037443338333242, 17, 7, 2),
(73, 0x43373445333837302d464433342d343535392d414142442d323734384137323630343946, 18, 7, 3),
(74, 0x30303835323066342d383138622d346533652d396464382d333930323663643366333830, 8, 4, 3),
(75, 0x36453846463531422d384333392d343044372d423531322d303338463841354639304543, 9, 4, 1),
(76, 0x36453846463531422d384333392d343044372d423531322d303338463841354639304543, 10, 4, 3),
(77, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, 11, 4, 1),
(78, '', 11, 2, 2),
(79, '', 12, 2, 2),
(80, '', 7, 5, 2),
(81, '', 8, 5, 2),
(82, '', 13, 2, 2),
(83, '', 19, 6, 2),
(84, '', 20, 6, 2),
(93, 0x41463237454230412d304144452d343042322d394638372d353544363037443338333242, 19, 7, 2),
(94, '', 14, 2, 2),
(95, '', 15, 2, 2),
(101, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, 21, 6, 2),
(102, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 22, 6, 2),
(103, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 23, 6, 2),
(104, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 24, 6, 2),
(105, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 25, 6, 2),
(106, 0x31333230384645302d354230442d344241332d413842302d364341394639303033333531, 26, 6, 1),
(107, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 27, 6, 2),
(108, 0x30383142324644332d304339422d343945382d384631352d453939384641424139443939, 28, 6, 1),
(109, 0x31333230384645302d354230442d344241332d413842302d364341394639303033333531, 29, 6, 3),
(110, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 30, 6, 2),
(111, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 31, 6, 2),
(112, 0x46383837443637442d433638442d343241362d423643322d323041344641424530343936, 32, 6, 1),
(113, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 33, 6, 2),
(114, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 34, 6, 2),
(115, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, 35, 6, 2),
(116, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 36, 6, 2),
(117, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 37, 6, 2),
(118, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 38, 6, 2),
(119, '', 12, 4, 2),
(120, '', 13, 4, 2),
(121, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 39, 6, 2),
(122, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 40, 6, 2),
(123, '', 14, 4, 2),
(124, '', 15, 4, 2),
(125, '', 3, 3, 2),
(126, '', 4, 3, 2),
(127, '', 5, 3, 2),
(128, '', 6, 3, 2),
(129, '', 16, 2, 2),
(130, '', 17, 2, 2),
(131, '', 41, 6, 2),
(132, '', 42, 6, 2),
(133, '', 43, 6, 2),
(134, '', 44, 6, 2),
(135, '', 45, 6, 2),
(136, '', 46, 6, 2),
(137, '', 20, 7, 2),
(138, '', 21, 7, 2),
(139, '', 22, 7, 2),
(140, '', 23, 7, 2),
(141, '', 24, 7, 2),
(142, '', 25, 7, 2),
(143, '', 26, 7, 2),
(144, 0x31414435393435392d393239452d343838462d383844442d433544424244314139343137, 18, 2, 1),
(145, 0x31414435393435392d393239452d343838462d383844442d433544424244314139343137, 19, 2, 3),
(146, 0x44353842414542392d313739342d343533312d424343422d334436463643354633364630, 20, 2, 1),
(147, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 21, 2, 2),
(148, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 22, 2, 2),
(149, 0x36423233334232332d374643462d343643342d383443412d333139344431433543413638, 23, 2, 1),
(150, 0x44353842414542392d313739342d343533312d424343422d334436463643354633364630, 24, 2, 3),
(151, 0x41363646433939412d413236312d343830442d413442372d384343414242343032314335, 25, 2, 1),
(152, 0x41363646433939412d413236312d343830442d413442372d384343414242343032314335, 26, 2, 3),
(153, 0x42433243444339452d414637322d343232392d384638352d333237393235413437434344, 27, 2, 1),
(154, 0x42433243444339452d414637322d343232392d384638352d333237393235413437434344, 28, 2, 3),
(155, 0x46464637343841362d394336382d344533302d414245452d384545424634333146333237, 29, 2, 1),
(156, 0x46464637343841362d394336382d344533302d414245452d384545424634333146333237, 30, 2, 3),
(157, 0x42333133353639312d393635332d343046452d383946312d384636454345374334463944, 31, 2, 1),
(158, 0x42333133353639312d393635332d343046452d383946312d384636454345374334463944, 32, 2, 3),
(159, 0x44343431364644412d333138362d344534332d384636382d394346333937363846343734, 33, 2, 1),
(160, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 34, 2, 2),
(161, 0x44304132324537372d393242362d344242372d383831452d423443453741373830394530, 35, 2, 1),
(162, 0x44343431364644412d333138362d344534332d384636382d394346333937363846343734, 36, 2, 3),
(163, 0x30393536443430382d463544372d344239342d383933342d444437383441323343323337, 37, 2, 1),
(164, 0x30393536443430382d463544372d344239342d383933342d444437383441323343323337, 38, 2, 3),
(165, 0x35433942423235362d463145332d343044462d423232352d304434323835394433354338, 39, 2, 1),
(166, 0x44304132324537372d393242362d344242372d383831452d423443453741373830394530, 40, 2, 3),
(167, 0x36304245384536442d413037462d344437332d413331372d344232363846314442313244, 41, 2, 1),
(168, 0x35433942423235362d463145332d343044462d423232352d304434323835394433354338, 42, 2, 3),
(169, 0x43333136464439442d344245372d344134392d394431372d454145444338354142414143, 43, 2, 1),
(170, 0x36423233334232332d374643462d343643342d383443412d333139344431433543413638, 44, 2, 3),
(171, 0x38414637353836422d433844432d343132332d394135312d383632384642373946384130, 45, 2, 1),
(172, 0x38414637353836422d433844432d343132332d394135312d383632384642373946384130, 46, 2, 3),
(173, 0x44453846393043392d454236452d343333462d414543312d453233383842384131383039, 47, 2, 1),
(174, 0x36304245384536442d413037462d344437332d413331372d344232363846314442313244, 48, 2, 3),
(175, 0x43314337434442392d444543352d344638362d393141362d363437303838434234363233, 49, 2, 1),
(176, 0x43333136464439442d344245372d344134392d394431372d454145444338354142414143, 50, 2, 3),
(177, 0x38433033364135312d373934412d343437422d423638312d333545343245373943433245, 51, 2, 1),
(178, 0x38433033364135312d373934412d343437422d423638312d333545343245373943433245, 52, 2, 3),
(179, 0x33363335383246392d443939422d343042342d413339462d463642394342464342343144, 53, 2, 1),
(180, 0x44453846393043392d454236452d343333462d414543312d453233383842384131383039, 54, 2, 3),
(181, 0x46374445413739422d303741382d343243302d394430392d374644434632383541314642, 55, 2, 1),
(182, '', 9, 5, 2),
(183, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, 16, 4, 2),
(184, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, 17, 4, 2),
(185, '', 1, 8, 2),
(186, '', 2, 8, 2),
(187, '', 3, 8, 2),
(188, 0x33353635363336342d363933342d343942352d393842452d453830453538374631454137, 27, 7, 1),
(189, 0x33353635363336342d363933342d343942352d393842452d453830453538374631454137, 28, 7, 2),
(190, '', 1, 9, 2),
(191, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, 18, 4, 2),
(192, '', 29, 7, 2),
(193, '', 19, 4, 2),
(194, '', 20, 4, 2),
(195, '', 21, 4, 2),
(196, 0x41343244353634332d333934442d344644352d424439452d433835444634353237363535, 22, 4, 1),
(197, '', 47, 6, 2),
(198, '', 48, 6, 2),
(199, '', 49, 6, 2),
(200, '', 50, 6, 2),
(201, 0x32433338463232462d433243392d343638332d384541362d373436413631424334344233, 56, 2, 1),
(202, 0x43394445454246322d383934362d343430442d423042352d333438443342443735303744, 30, 7, 1),
(203, 0x38333937393138442d333936362d344435302d393941462d423439443731443541374145, 31, 7, 1),
(204, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, 4, 8, 1),
(205, 0x32334143353233452d383136392d343038322d383339432d444635303831443145413230, 5, 8, 1),
(206, 0x32334143353233452d383136392d343038322d383339432d444635303831443145413230, 6, 8, 2),
(207, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, 7, 8, 2),
(208, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, 8, 8, 2),
(209, 0x32433338463232462d433243392d343638332d384541362d373436413631424334344233, 57, 2, 2),
(210, 0x43394445454246322d383934362d343430442d423042352d333438443342443735303744, 32, 7, 3),
(211, 0x42463838323634342d393736332d344638452d393238412d463537443232393245334435, 33, 7, 1),
(212, 0x39373342453637372d453634352d343030332d414530442d433234383643453738453242, 58, 2, 1),
(213, 0x39373342453637372d453634352d343030332d414530442d433234383643453738453242, 59, 2, 2),
(214, 0x39373342453637372d453634352d343030332d414530442d433234383643453738453242, 60, 2, 2),
(215, 0x42463838323634342d393736332d344638452d393238412d463537443232393245334435, 34, 7, 2),
(216, 0x38333937393138442d333936362d344435302d393941462d423439443731443541374145, 35, 7, 2),
(217, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, 9, 8, 2),
(218, '', 10, 8, 2),
(219, '', 11, 8, 2),
(220, '', 1, 10, 2),
(221, '', 2, 10, 2),
(222, '', 3, 10, 2),
(223, '', 4, 10, 2),
(224, '', 5, 10, 2),
(225, '', 6, 10, 2),
(226, '', 7, 10, 2),
(227, '', 8, 10, 2),
(228, '', 9, 10, 2),
(229, '', 10, 10, 2),
(230, '', 11, 10, 2),
(231, '', 12, 10, 2),
(232, '', 13, 10, 2),
(233, '', 14, 10, 2),
(234, '', 15, 10, 2),
(235, '', 16, 10, 2),
(236, '', 17, 10, 2),
(237, '', 18, 10, 2),
(238, '', 19, 10, 2),
(239, '', 20, 10, 2),
(240, '', 21, 10, 2),
(241, '', 22, 10, 2),
(242, '', 23, 10, 2),
(243, '', 24, 10, 2),
(244, '', 25, 10, 2),
(245, '', 26, 10, 2),
(246, '', 27, 10, 2),
(247, '', 28, 10, 2),
(248, '', 29, 10, 2),
(249, '', 30, 10, 2),
(250, '', 31, 10, 2),
(251, '', 32, 10, 2),
(252, '', 33, 10, 2),
(253, '', 34, 10, 2),
(254, 0x36343345433337422d423242462d344346392d424237302d444230364532313932353435, 35, 10, 1),
(255, '', 36, 10, 2),
(256, '', 37, 10, 2),
(257, 0x36343345433337422d423242462d344346392d424237302d444230364532313932353435, 38, 10, 2),
(258, 0x46314439443632462d333137362d344331332d383543372d463536383433364639364530, 61, 2, 1),
(259, 0x36343345433337422d423242462d344346392d424237302d444230364532313932353435, 39, 10, 2),
(260, '', 10, 5, 2);

-- --------------------------------------------------------

--
-- Structure de la table `calendarinstances`
--

CREATE TABLE `calendarinstances` (
  `id` int(10) UNSIGNED NOT NULL,
  `calendarid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `principaluri` varbinary(100) DEFAULT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = owner, 2 = read, 3 = readwrite',
  `displayname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `calendarorder` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `calendarcolor` varbinary(10) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  `present` tinyint(1) NOT NULL DEFAULT 1,
  `timezone` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `transparent` tinyint(1) NOT NULL DEFAULT 0,
  `share_href` varbinary(100) DEFAULT NULL,
  `share_displayname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `share_invitestatus` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1 = noresponse, 2 = accepted, 3 = declined, 4 = invalid',
  `grpid` mediumint(9) NOT NULL DEFAULT 0,
  `cal_type` tinyint(2) NOT NULL DEFAULT 1 COMMENT '1 = normal, 2 = room, 3 = computer, 4 = video'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `calendarinstances`
--

INSERT INTO `calendarinstances` (`id`, `calendarid`, `principaluri`, `access`, `displayname`, `uri`, `description`, `calendarorder`, `calendarcolor`, `visible`, `present`, `timezone`, `transparent`, `share_href`, `share_displayname`, `share_invitestatus`, `grpid`, `cal_type`) VALUES
(1, 1, 0x7072696e636970616c732f61646d696e, 1, 'From EcclesiaCRM3 without Group', 0x39323436433639352d313036432d343545422d384230302d433631343336384636334244, NULL, 0, 0x23303030303030, 1, 0, NULL, 1, NULL, NULL, 2, 9, 1),
(2, 2, 0x7072696e636970616c732f61646d696e, 1, '0 -3 ans', 0x38636664343636382d363731642d343230362d383430632d653965333266316538613438, NULL, 0, 0x23636431376632, 1, 1, NULL, 1, NULL, NULL, 2, 2, 1),
(3, 3, 0x7072696e636970616c732f61646d696e, 1, 'Groupe de maison Strasbourg centre', 0x63656330656330632d666666612d343163302d626435332d323734643133633766616230, NULL, 0, 0x23373762383432, 1, 1, NULL, 1, NULL, NULL, 2, 3, 1),
(4, 4, 0x7072696e636970616c732f61646d696e, 1, 'Culte', 0x64613465353138372d656133652d343164612d393835312d663830663239633662303563, NULL, 0, 0x23323465623062, 1, 1, NULL, 1, NULL, NULL, 2, 4, 1),
(5, 5, 0x7072696e636970616c732f61646d696e, 1, 'Conseil spirituel', 0x62356133636162342d646264362d343635382d393835642d343736366538323235626333, NULL, 0, 0x23663237383166, 1, 1, NULL, 1, NULL, NULL, 2, 7, 1),
(6, 6, 0x7072696e636970616c732f61646d696e, 1, 'class test', 0x37656461333938312d393037362d343065372d626136652d616165323535623133656561, NULL, 0, 0x23313832666635, 1, 1, NULL, 1, NULL, NULL, 2, 8, 1),
(7, 7, 0x7072696e636970616c732f61646d696e, 1, 'Mon premier calendrier perso', 0x38313832373436342d303436342d343643452d393734432d323641353931394535443138, NULL, 0, 0x23303034636635, 1, 1, NULL, 1, NULL, NULL, 2, 0, 1),
(8, 8, 0x7072696e636970616c732f61646d696e, 1, 'Bobby', 0x42324445343530362d383346442d343932392d423342322d333744443633444331453438, NULL, 0, 0x23656431633734, 1, 1, NULL, 1, NULL, NULL, 2, 0, 1),
(9, 9, 0x7072696e636970616c732f61646d696e, 1, 'test', 0x46384145433035342d343238432d344344352d424542362d464137343941443439433636, NULL, 0, NULL, 0, 1, NULL, 1, NULL, NULL, 2, 10, 1),
(10, 10, 0x7072696e636970616c732f61646d696e, 1, 'Computor 1', 0x31303345323434432d394633302d343544452d413643442d373444443939443344304332, 'Room 213', 0, 0x23366665363237, 1, 1, NULL, 1, NULL, NULL, 2, 0, 3);

-- --------------------------------------------------------

--
-- Structure de la table `calendars`
--

CREATE TABLE `calendars` (
  `id` int(10) UNSIGNED NOT NULL,
  `synctoken` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `components` varbinary(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `calendars`
--

INSERT INTO `calendars` (`id`, `synctoken`, `components`) VALUES
(1, 3, 0x564556454e54),
(2, 62, 0x564556454e54),
(3, 7, 0x564556454e54),
(4, 23, 0x564556454e54),
(5, 11, 0x564556454e54),
(6, 51, 0x564556454e54),
(7, 36, 0x564556454e54),
(8, 12, 0x564556454e54),
(9, 2, 0x564556454e54),
(10, 40, 0x564556454e54);

-- --------------------------------------------------------

--
-- Structure de la table `calendarsubscriptions`
--

CREATE TABLE `calendarsubscriptions` (
  `id` int(11) UNSIGNED NOT NULL,
  `uri` varbinary(200) NOT NULL,
  `principaluri` varbinary(100) NOT NULL,
  `source` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refreshrate` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `calendarorder` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `calendarcolor` varbinary(10) DEFAULT NULL,
  `striptodos` tinyint(1) DEFAULT NULL,
  `stripalarms` tinyint(1) DEFAULT NULL,
  `stripattachments` tinyint(1) DEFAULT NULL,
  `lastmodified` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `canvassdata_can`
--

CREATE TABLE `canvassdata_can` (
  `can_ID` mediumint(9) UNSIGNED NOT NULL,
  `can_famID` mediumint(9) NOT NULL DEFAULT 0,
  `can_Canvasser` mediumint(9) NOT NULL DEFAULT 0,
  `can_FYID` mediumint(9) DEFAULT NULL,
  `can_date` date DEFAULT NULL,
  `can_Positive` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_Critical` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_Insightful` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_Financial` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_Suggestion` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `can_NotInterested` tinyint(1) NOT NULL DEFAULT 0,
  `can_WhyNotInterested` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cards`
--

CREATE TABLE `cards` (
  `id` int(11) UNSIGNED NOT NULL,
  `addressbookid` int(11) UNSIGNED NOT NULL,
  `carddata` mediumblob DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `lastmodified` int(11) UNSIGNED DEFAULT NULL,
  `etag` varbinary(32) DEFAULT NULL,
  `size` int(11) UNSIGNED NOT NULL,
  `personId` mediumint(9) NOT NULL DEFAULT -1 COMMENT '-1 personal cards, >1 for a real person in the CRM'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `cards`
--

INSERT INTO `cards` (`id`, `addressbookid`, `carddata`, `uri`, `lastmodified`, `etag`, `size`, `personId`) VALUES
(1, 1, 0x424547494e3a56434152440a56455253494f4e3a332e300a50524f4449443a2d2f2f4170706c6520496e632e2f2f4d6163204f5320582031302e31322e362f2f454e0a4e3a416c6578616e6465723b526f68616e3b3b3b0a464e3a526f68616e20416c6578616e6465720a454d41494c3b747970653d494e5445524e45543b747970653d574f524b3b747970653d707265663a726f68616e2e616c65784076697267696e2e6e65740a454d41494c3b747970653d494e5445524e45543b747970653d484f4d453b747970653d707265663a726f68616e2e616c65784076697267696e2e6e65740a6974656d312e4144523b747970653d484f4d453b747970653d707265663a3b3b3b3b3b0a6974656d312e582d41424144523a66720a5549443a66643962633237342d343432622d343536352d623136392d3331306537646361343138340a454e443a5643415244, 0x555549442d38323963326338372d643666632d343036642d393739312d303635366664333731313465, 1548993127, 0x6538646637396536346634386531323130386536343164316533633665666265, 334, 6),
(2, 1, 0x424547494e3a56434152440a56455253494f4e3a332e300a50524f4449443a2d2f2f4170706c6520496e632e2f2f4d6163204f5320582031302e31322e362f2f454e0a4e3a426574613b5069657272653b3b3b0a464e3a50696572726520426574610a6974656d312e4144523b747970653d484f4d453b747970653d707265663a3b3b313020727565206465206c612076696c6c653b536f756666656c7765796572736865696d3b3b36373436300a6974656d312e582d41424144523a66720a5549443a39306239656232382d383332612d346338362d623037322d3866366234356432376135620a454e443a5643415244, 0x555549442d32663232316164382d376533642d346165342d386436372d313661633132393033336338, 1548993127, 0x3432616439643230356639343134303462613766613638663235666437393230, 242, 9),
(3, 1, 0x424547494e3a56434152440a56455253494f4e3a332e300a50524f4449443a2d2f2f4170706c6520496e632e2f2f4d6163204f5320582031302e31322e362f2f454e0a4e3a426574613b416e746f6e696f3b3b3b0a464e3a416e746f6e696f20426574610a6974656d312e4144523b747970653d484f4d453b747970653d707265663a3b3b313020727565206465206c612076696c6c653b536f756666656c7765796572736865696d3b3b36373436300a6974656d312e582d41424144523a66720a5549443a63663336393162312d376530342d343536382d616462352d3233346436303662633064380a454e443a5643415244, 0x555549442d61666431666436392d383866362d343430662d383562622d326466393239636463323939, 1548993127, 0x3637633562316532313231353434623737316431313137663831643235353161, 244, 12),
(5, 5, 0x424547494e3a56434152440a56455253494f4e3a332e300a50524f4449443a2d2f2f4170706c6520496e632e2f2f4d6163204f5320582031302e31322e362f2f454e0a4e3a416c7068613b48656e7269657474653b3b3b0a464e3a48656e72696574746520416c7068610a454d41494c3b747970653d494e5445524e45543b747970653d484f4d453b747970653d707265663a68656e72696574746540746f746f2e66720a6974656d312e4144523b747970653d484f4d453b747970653d707265663a3b3b3132206176656e7565206465204575726f70653b5374726173626f7572673b3b36373030300a6974656d312e582d41424144523a66720a5549443a34353235613730382d356165342d343738342d386339322d6535383135393831613934310a454e443a5643415244, 0x555549442d65343337613736662d383734372d346163642d383533362d663532313464393238636437, 1548993127, 0x6532326331373762313266323739346334343335626163623331393764633261, 302, 5),
(6, 5, 0x424547494e3a56434152440a56455253494f4e3a332e300a50524f4449443a2d2f2f4170706c6520496e632e2f2f4d6163204f5320582031302e31322e362f2f454e0a4e3a416c7068613b48656e72693b3b3b0a464e3a48656e726920416c7068610a6974656d312e4144523b747970653d484f4d453b747970653d707265663a3b3b3132206176656e7565206465204575726f70653b5374726173626f7572673b3b36373030300a6974656d312e582d41424144523a66720a5549443a39663264393737662d326531642d343963302d626161372d6563313934373861383963620a454e443a5643415244, 0x555549442d62633562373361622d313065302d346339662d623062362d636434386461393932623330, 1548993127, 0x3962653130363565663535336433373637646133366363393037363464323566, 236, 18);

-- --------------------------------------------------------

--
-- Structure de la table `church_location`
--

CREATE TABLE `church_location` (
  `location_id` int(11) NOT NULL,
  `location_typeId` int(11) NOT NULL,
  `location_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `location_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `location_city` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `location_state` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `location_zip` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `location_country` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `location_phone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_timzezone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `church_location_person`
--

CREATE TABLE `church_location_person` (
  `location_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `person_location_role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `church_location_role`
--

CREATE TABLE `church_location_role` (
  `location_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `role_order` int(11) NOT NULL,
  `role_title` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ckeditor_templates`
--

CREATE TABLE `ckeditor_templates` (
  `cke_tmp_id` mediumint(9) UNSIGNED NOT NULL,
  `cke_tmp_per_ID` mediumint(9) UNSIGNED NOT NULL,
  `cke_tmp_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cke_tmp_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cke_tmp_text` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `cke_tmp_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `ckeditor_templates`
--

INSERT INTO `ckeditor_templates` (`cke_tmp_id`, `cke_tmp_per_ID`, `cke_tmp_title`, `cke_tmp_desc`, `cke_tmp_text`, `cke_tmp_image`) VALUES
(1, 1, 'Mon premier Modèle', 'Voici un exemple', '<p>&nbsp;</p><table><tbody><tr><td><p>D&Eacute;TAILS G&Eacute;N&Eacute;RAUX SUR LA NATURE DU CULTE</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>Pr&eacute;sidence du culte</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Th&egrave;me</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Objectif du culte</p></td><td><p>.</p></td></tr><tr><td><p>Culte calendaire</p><p>Exemple : Dimanche de la mission, culte de famille, culte de P&acirc;que, Pentec&ocirc;te, No&euml;l...</p></td><td><p>/</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>ORATEUR/ORATRICE</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>Nom &amp; Pr&eacute;nom</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Titre du message</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Texte(s) biblique(s) + version(s)</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>R&eacute;sum&eacute;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Support(s) utilis&eacute;(s)</p><p>Exemples : powerpoint, mp3, vid&eacute;o...</p></td><td><p>/</p></td></tr><tr><td><p>Autre(s) type(s) de support</p><p>Exemples : danse, intervention d&#39;un autre personne...</p></td><td><p>/</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>D&Eacute;ROUL&Eacute; INDICATIF</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td rowspan=\"2\"><p>Timing indicatif en minutes</p><p>(1h30 maximum par culte)</p></td><td rowspan=\"2\"><p>Objet</p></td><td colspan=\"2\"><p>Intervenant(s)</p></td></tr><tr><td><p>Culte 9h00</p></td><td><p>Culte 11h00</p></td></tr><tr><td><p>.25&#39;</p></td><td><p>Louange</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.5&#39;</p></td><td><p>Si possible</p><p>au culte de 11h00</p><p>Pr&eacute;sentation des monos apr&egrave;s le temps de louange</p><p>&nbsp;</p></td><td><p>/</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.5&#39;</p></td><td><p>Pri&egrave;re pour les monos et enfants</p><p>au culte de 11h00</p><p>puis les enfants seront invit&eacute;s &agrave; descendre en salle D. Scott pour la projection d&#39;un film</p></td><td><p>/</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.10&#39;</p></td><td><p>Ste C&egrave;ne/Offrandes</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.30&#39;</p></td><td><p>Message</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.5&#39;</p></td><td><p>Appel/Pri&egrave;res</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.5&#39;</p></td><td><p>Annonces</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>.5&#39;</p></td><td><p>Rentr&eacute;e Epis&#39;ode</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr></tbody></table><table><tbody><tr><td><p>ANNONCE(S) MICRO PAR CELUI QUI PR&Eacute;SIDE</p><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td rowspan=\"4\"><p>5&#39;</p></td><td><p>Livret contact</p></td><td><p>Si vous d&eacute;sirez d&eacute;couvrir L&#39;&Eacute;PIS en toute simplicit&eacute;, n&#39;h&eacute;sitez pas &agrave; vous rendre au point accueil de l&#39;&eacute;glise qui se trouve dans le hall (livret contact offert, une bible si vous n&#39;en avez pas encore...)</p></td></tr><tr><td><p>&nbsp;</p></td><td><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></td></tr><tr><td><p>&nbsp;</p></td><td><p>&nbsp;</p><p>&nbsp;</p></td></tr><tr><td><p>&nbsp;</p></td><td><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><p>&nbsp;</p><table><tbody><tr><td><p>ANNONCE MICRO PAR UN AUTRE INTERVENANT</p><p>1 maximum</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td rowspan=\"3\"><p>3&#39;</p></td><td><p>Nom &amp; Pr&eacute;nom</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Objet de l&#39;annonce</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Support(s)</p><p>Exemples : powerpoint, vid&eacute;o...</p></td><td><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>ACCUEIL</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>&nbsp;</p></td><td><p>Culte&nbsp;9h00</p></td><td><p>Culte 11h00</p></td></tr><tr><td><p>Information(s) pour l&#39;accueil</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Accueil Grande Salle</p></td><td><p>.</p></td><td><p>.&nbsp;</p></td></tr><tr><td><p>Accueil Point Accueil</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Accueil Parking</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Accueil S&eacute;curit&eacute;</p></td><td><p>.</p></td><td><p>.</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>LOUANGE</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>Leader de louange</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) pour le leader de louange</p></td><td><p>.</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>PRODUCTION</p><p>Son - Vid&eacute;o - Lumi&egrave;re - Projection - Diffusion</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>&nbsp;</p></td><td><p>Culte 9h00</p></td><td><p>Culte 11h00</p></td></tr><tr><td><p>Production</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) pour la production</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Technicien(s) du son</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) pour&nbsp;le son</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Technicien(s) vid&eacute;o</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) la&nbsp;vid&eacute;o</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Technicien(s) lumi&egrave;re</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) la&nbsp;lumi&egrave;re</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Technicien(s) projection</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) pour la&nbsp;projection</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Technicien(s) diffusion</p></td><td><p>.</p></td><td><p>.</p></td></tr><tr><td><p>Information(s) pour la diffusion</p></td><td><p>.</p></td><td><p>.</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>SAINTE C&Egrave;NE</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>&nbsp;</p></td><td><p>Culte 9h00</p></td><td><p>Culte 11h00</p></td></tr><tr><td><p>Distribution Sainte c&egrave;ne</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr><tr><td><p>Lecture biblique</p></td><td><p>&nbsp;</p></td><td><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>OFFRANDE</p></td></tr></tbody></table><p>&nbsp;</p><table><tbody><tr><td><p>&nbsp;</p></td><td><p>Culte 9h00</p></td><td><p>Culte 11h00</p></td></tr><tr><td><p>R&eacute;colte offrande</p></td><td><p>Freddy Freyd</p></td><td><p>Michel Mureau (+ &eacute;quipe)</p></td></tr><tr><td><p>Information(s) pour les offrande(s)</p></td><td><p>.</p></td><td><p>.</p></td></tr></tbody></table><p>&nbsp;</p><p>8) voici le r&eacute;sultat :</p>', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `config_cfg`
--

CREATE TABLE `config_cfg` (
  `cfg_id` int(11) NOT NULL DEFAULT 0,
  `cfg_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cfg_value` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `config_cfg`
--

INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(4, 'sLogLevel', '0'),
(23, 'sDefaultCountry', 'France'),
(28, 'bSMTPAuth', ''),
(39, 'sLanguage', 'fr_FR'),
(45, 'iChurchLatitude', '48.6163782'),
(46, 'iChurchLongitude', '7.7528169'),
(48, 'bHideFriendDate', ''),
(49, 'bHideFamilyNewsletter', ''),
(50, 'bHideWeddingDate', ''),
(51, 'bHideLatLon', ''),
(52, 'bUseDonationEnvelopes', ''),
(58, 'bUseScannedChecks', ''),
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Paris'),
(67, 'bForceUppercaseZip', ''),
(72, 'bEnableNonDeductible', ''),
(80, 'bEnableSelfRegistration', ''),
(102, 'sDateFormatLong', 'd/m/Y'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(112, 'sTimeFormat', '%H:%M'),
(999, 'bRegistered', ''),
(1003, 'sChurchName', 'Mon église'),
(1004, 'sChurchAddress', '1 rue Robert Kieffer'),
(1005, 'sChurchCity', 'Bischheim'),
(1007, 'sChurchZip', '67800'),
(1027, 'sPledgeSummary2', 'as of'),
(1028, 'sDirectoryDisclaimer1', 'Every effort was made to insure the accuracy of this directory.  If there are any errors or omissions, please contact the church office.This directory is for the use of the people of'),
(1035, 'bEnableGravatarPhotos', ''),
(1036, 'bEnableExternalBackupTarget', ''),
(1037, 'sExternalBackupType', 'WebDAV'),
(1046, 'sLastIntegrityCheckTimeStamp', '2018-09-27 22:38:47'),
(1047, 'sChurchCountry', 'France'),
(1051, 'bTimeEnglish', ''),
(2010, 'bAllowEmptyLastName', ''),
(2017, 'bEnableExternalCalendarAPI', '1'),
(2045, 'bPHPMailerAutoTLS', ''),
(2046, 'sPHPMailerSMTPSecure', ''),
(2048, 'iPersonAddressStyle', '1'),
(2049, 'bCheckedAttendeesCurrentUser', ''),
(2050, 'bStateUnusefull', ''),
(2051, 'sCurrency', '€'),
(2057, 'bGDPR', '1'),
(2070, 'bEnabledMenuLinks', '1'),
(2078, 'bEnabledDavWebBrowser', ''),
(20142, 'bHSTSEnable', '');

-- --------------------------------------------------------

--
-- Structure de la table `deposit_dep`
--

CREATE TABLE `deposit_dep` (
  `dep_ID` mediumint(9) UNSIGNED NOT NULL,
  `dep_Date` date DEFAULT NULL,
  `dep_Comment` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `dep_EnteredBy` mediumint(9) UNSIGNED DEFAULT NULL,
  `dep_Closed` tinyint(1) NOT NULL DEFAULT 0,
  `dep_Type` enum('Bank','CreditCard','BankDraft','eGive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Bank',
  `dep_Fund` mediumint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

--
-- Déchargement des données de la table `deposit_dep`
--

INSERT INTO `deposit_dep` (`dep_ID`, `dep_Date`, `dep_Comment`, `dep_EnteredBy`, `dep_Closed`, `dep_Type`, `dep_Fund`) VALUES
(1, '2018-01-03', 'essai', NULL, 0, 'Bank', 0),
(4, '2018-01-11', 'Encore un dépôt', NULL, 0, 'CreditCard', 0),
(5, '2018-01-20', 'un dernier dépôt', NULL, 0, 'Bank', 0),
(6, '2018-02-03', 'essai', NULL, 1, 'BankDraft', 0),
(7, '2018-02-05', 'Test SF', NULL, 0, 'Bank', 0),
(8, '2018-08-16', '', NULL, 0, 'Bank', 0),
(9, '2019-02-03', 'Essai 566', NULL, 0, 'Bank', 0),
(10, '2019-02-03', 'essai 567', NULL, 0, 'CreditCard', 0),
(11, '2019-02-03', 'Un dernier', NULL, 0, 'Bank', 0);

-- --------------------------------------------------------

--
-- Structure de la table `donateditem_di`
--

CREATE TABLE `donateditem_di` (
  `di_ID` mediumint(9) UNSIGNED NOT NULL,
  `di_item` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `di_FR_ID` mediumint(9) UNSIGNED NOT NULL,
  `di_donor_ID` mediumint(9) NOT NULL DEFAULT 0,
  `di_buyer_ID` mediumint(9) NOT NULL DEFAULT 0,
  `di_multibuy` smallint(1) NOT NULL DEFAULT 0,
  `di_title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `di_description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `di_sellprice` decimal(8,2) DEFAULT NULL,
  `di_estprice` decimal(8,2) DEFAULT NULL,
  `di_minimum` decimal(8,2) DEFAULT NULL,
  `di_materialvalue` decimal(8,2) DEFAULT NULL,
  `di_EnteredBy` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `di_EnteredDate` date NOT NULL,
  `di_picture` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donationfund_fun`
--

CREATE TABLE `donationfund_fun` (
  `fun_ID` tinyint(3) NOT NULL,
  `fun_Active` enum('true','false') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'true',
  `fun_Name` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fun_Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `donationfund_fun`
--

INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
(1, 'true', 'fond de commerce', 'Ma descrition'),
(2, 'true', 'Dime', 'fait à l\'église');

-- --------------------------------------------------------

--
-- Structure de la table `egive_egv`
--

CREATE TABLE `egive_egv` (
  `egv_egiveID` varchar(16) CHARACTER SET utf8 NOT NULL,
  `egv_famID` mediumint(9) UNSIGNED NOT NULL,
  `egv_DateEntered` datetime NOT NULL,
  `egv_DateLastEdited` datetime NOT NULL,
  `egv_EnteredBy` smallint(6) NOT NULL DEFAULT 0,
  `egv_EditedBy` smallint(6) NOT NULL DEFAULT 0,
  `egv_ID` mediumint(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `email_count`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `email_count` (
`email` varchar(100)
,`total` bigint(21)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `email_list`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `email_list` (
`email` varchar(100)
,`type` varchar(11)
,`id` mediumint(9) unsigned
);

-- --------------------------------------------------------

--
-- Structure de la table `email_message_pending_emp`
--

CREATE TABLE `email_message_pending_emp` (
  `emp_usr_id` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `emp_to_send` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `emp_subject` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `emp_message` text COLLATE utf8_unicode_ci NOT NULL,
  `emp_attach_name` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `emp_attach` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `email_recipient_pending_erp`
--

CREATE TABLE `email_recipient_pending_erp` (
  `erp_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `erp_usr_id` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `erp_num_attempt` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `erp_failed_time` datetime DEFAULT NULL,
  `erp_email_address` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `eventcountnames_evctnm`
--

CREATE TABLE `eventcountnames_evctnm` (
  `evctnm_countid` int(5) NOT NULL,
  `evctnm_eventtypeid` int(11) DEFAULT NULL,
  `evctnm_countname` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `evctnm_notes` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `eventcountnames_evctnm`
--

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
(1, 1, 'Total', ''),
(2, 1, 'Members', ''),
(3, 1, 'Visitors', ''),
(4, 2, 'Total', ''),
(5, 2, 'Members', ''),
(6, 2, 'Visitors', ''),
(7, 1, 'Enfants', '');

-- --------------------------------------------------------

--
-- Structure de la table `eventcounts_evtcnt`
--

CREATE TABLE `eventcounts_evtcnt` (
  `evtcnt_eventid` int(11) NOT NULL DEFAULT 0,
  `evtcnt_countid` int(5) NOT NULL DEFAULT 0,
  `evtcnt_countname` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `evtcnt_countcount` int(6) DEFAULT NULL,
  `evtcnt_notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `eventcounts_evtcnt`
--

INSERT INTO `eventcounts_evtcnt` (`evtcnt_eventid`, `evtcnt_countid`, `evtcnt_countname`, `evtcnt_countcount`, `evtcnt_notes`) VALUES
(74, 4, 'Total', 0, ''),
(74, 5, 'Members', 0, ''),
(74, 6, 'Visitors', 0, ''),
(75, 4, 'Total', 0, ''),
(75, 5, 'Members', 0, ''),
(75, 6, 'Visitors', 0, ''),
(77, 1, 'Total', 0, ''),
(77, 2, 'Members', 0, ''),
(77, 3, 'Visitors', 0, ''),
(77, 7, 'Enfants', 0, ''),
(96, 4, 'Total', 0, ''),
(96, 5, 'Members', 0, ''),
(96, 6, 'Visitors', 0, ''),
(97, 4, 'Total', 0, ''),
(97, 5, 'Members', 0, ''),
(97, 6, 'Visitors', 0, ''),
(99, 4, 'Total', 0, ''),
(99, 5, 'Members', 0, ''),
(99, 6, 'Visitors', 0, ''),
(101, 4, 'Total', 0, ''),
(101, 5, 'Members', 0, ''),
(101, 6, 'Visitors', 0, ''),
(102, 4, 'Total', 0, ''),
(102, 5, 'Members', 0, ''),
(102, 6, 'Visitors', 0, ''),
(105, 4, 'Total', 0, ''),
(105, 5, 'Members', 0, ''),
(105, 6, 'Visitors', 0, ''),
(109, 4, 'Total', 0, ''),
(109, 5, 'Members', 0, ''),
(109, 6, 'Visitors', 0, ''),
(110, 4, 'Total', 0, ''),
(110, 5, 'Members', 0, ''),
(110, 6, 'Visitors', 0, ''),
(126, 4, 'Total', 0, ''),
(126, 5, 'Members', 0, ''),
(126, 6, 'Visitors', 0, ''),
(128, 4, 'Total', 0, ''),
(128, 5, 'Members', 0, ''),
(128, 6, 'Visitors', 0, ''),
(129, 4, 'Total', 0, ''),
(129, 5, 'Members', 0, ''),
(129, 6, 'Visitors', 0, ''),
(130, 4, 'Total', 0, ''),
(130, 5, 'Members', 0, ''),
(130, 6, 'Visitors', 0, ''),
(131, 1, 'Total', 95, ''),
(131, 2, 'Members', 60, ''),
(131, 3, 'Visitors', 5, ''),
(131, 7, 'Enfants', 30, ''),
(132, 4, 'Total', 0, ''),
(132, 5, 'Members', 0, ''),
(132, 6, 'Visitors', 0, ''),
(134, 4, 'Total', 0, ''),
(134, 5, 'Members', 0, ''),
(134, 6, 'Visitors', 0, ''),
(135, 4, 'Total', 0, ''),
(135, 5, 'Members', 0, ''),
(135, 6, 'Visitors', 0, ''),
(136, 4, 'Total', 0, ''),
(136, 5, 'Members', 0, ''),
(136, 6, 'Visitors', 0, ''),
(137, 4, 'Total', 0, ''),
(137, 5, 'Members', 0, ''),
(137, 6, 'Visitors', 0, ''),
(138, 4, 'Total', 0, ''),
(138, 5, 'Members', 0, ''),
(138, 6, 'Visitors', 0, ''),
(139, 4, 'Total', 0, ''),
(139, 5, 'Members', 0, ''),
(139, 6, 'Visitors', 0, ''),
(140, 4, 'Total', 0, ''),
(140, 5, 'Members', 0, ''),
(140, 6, 'Visitors', 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `events_event`
--

CREATE TABLE `events_event` (
  `event_id` int(11) NOT NULL,
  `event_type` int(11) NOT NULL DEFAULT 0,
  `event_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_text` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_start` datetime NOT NULL,
  `event_end` datetime NOT NULL,
  `inactive` int(1) NOT NULL DEFAULT 0,
  `event_typename` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_grpid` mediumint(9) DEFAULT NULL,
  `event_last_occurence` datetime NOT NULL,
  `event_location` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_calendardata` mediumblob DEFAULT NULL,
  `event_uri` varbinary(200) DEFAULT NULL,
  `event_calendarid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `event_lastmodified` int(11) UNSIGNED DEFAULT NULL,
  `event_etag` varbinary(32) DEFAULT NULL,
  `event_size` int(11) UNSIGNED NOT NULL,
  `event_componenttype` varbinary(8) DEFAULT NULL,
  `event_uid` varbinary(200) DEFAULT NULL,
  `event_coordinates` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `events_event`
--

INSERT INTO `events_event` (`event_id`, `event_type`, `event_title`, `event_desc`, `event_text`, `event_start`, `event_end`, `inactive`, `event_typename`, `event_grpid`, `event_last_occurence`, `event_location`, `event_calendardata`, `event_uri`, `event_calendarid`, `event_lastmodified`, `event_etag`, `event_size`, `event_componenttype`, `event_uid`, `event_coordinates`) VALUES
(56, 2, '0 -3 ans 17/02/2018', 'Créer à partir de la vue école du dimanche', '<p>Pr&eacute;sence</p>', '2018-02-25 22:20:38', '2018-02-25 22:20:38', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a33636436363964342d366664312d346464392d616165372d6338616666396135663761610d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830323235543232323033380d0a4454454e443a3230313830323235543232323033380d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a4372c3a9657220c3a020706172746972206465206c612076756520c3a9636f6c652064752064696d616e6368650d0a53554d4d4152593a30202d3320616e732031372f30322f323031380d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x33636436363964342d366664312d346464392d616165372d633861666639613566376161, 2, 1525635356, 0x6465313130653462316234666665633637623964616462636466303966366566, 421, 0x564556454e54, 0x33636436363964342d366664312d346464392d616165372d633861666639613566376161, ''),
(57, 2, '0 -3 ans 18/02/2018', 'Créer à partir de la vue école du dimanche', '<p>Pr&eacute;sence</p>', '2018-02-18 11:51:53', '2018-02-18 11:51:53', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a61366435366161332d306136622d343462632d396337622d6163303966666463313961660d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830323138543131353135330d0a4454454e443a3230313830323138543131353135330d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a4372c3a9657220c3a020706172746972206465206c612076756520c3a9636f6c652064752064696d616e6368650d0a53554d4d4152593a30202d3320616e732031382f30322f323031380d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x61366435366161332d306136622d343462632d396337622d616330396666646331396166, 2, 1525635356, 0x3839313839636437383965616635643937623535383637396538303537373664, 421, 0x564556454e54, 0x61366435366161332d306136622d343462632d396337622d616330396666646331396166, ''),
(74, 2, 'Par trimestre', 'juste pour tester', '', '2018-02-20 00:00:00', '2018-02-21 00:00:00', 0, 'Ecole du dimanche', 4, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a64366464396366382d636437332d343661312d626130372d3532323339386636353031630d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830323230543030303030300d0a4454454e443a3230313830323231543030303030300d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a6a7573746520706f7572207465737465720d0a53554d4d4152593a506172207472696d65737472650d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x64366464396366382d636437332d343661312d626130372d353232333938663635303163, 4, 1525635356, 0x3766343638333836623261633039336332633466373062656565356133336639, 387, 0x564556454e54, 0x64366464396366382d636437332d343661312d626130372d353232333938663635303163, ''),
(75, 2, 'Par trimestre', 'juste pour tester', '', '2018-05-06 03:00:00', '2018-05-06 14:30:00', 0, 'Ecole du dimanche', 4, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a30346564653632652d373731652d343538322d386566392d3635326365363463386334350d0a44545354414d503a3230313830353036543231333535370d0a435245415445443a3230313830353036543231333535370d0a4445534352495054494f4e3a6a7573746520706f7572207465737465720d0a53554d4d4152593a506172207472696d65737472650d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a445453544152543a3230313830353036543033303030300d0a4454454e443a3230313830353036543134333030300d0a4c4153542d4d4f4449464945443a3230313830353036543231333832320d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, 4, 1525635502, 0x3031343064316333396262353762373530616664396339623439393436373966, 387, 0x564556454e54, 0x30346564653632652d373731652d343538322d386566392d363532636536346338633435, ''),
(77, 1, 'essai groupe centre', 'coucou', '', '2018-01-31 00:00:00', '2018-02-01 00:00:00', 0, 'Service d\'église', 3, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a39323361366564392d356134322d346166352d386637652d3363346530633032396632310d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830313331543030303030300d0a4454454e443a3230313830323031543030303030300d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a636f75636f750d0a53554d4d4152593a65737361692067726f7570652063656e7472650d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x39323361366564392d356134322d346166352d386637652d336334653063303239663231, 3, 1525635356, 0x3765616332313865343830323262346463666265383662373262646530396133, 382, 0x564556454e54, 0x39323361366564392d356134322d346166352d386637652d336334653063303239663231, ''),
(86, 2, '0 -3 ans 26/02/2018', 'Créer à partir de la vue école du dimanche', '<p>Pr&eacute;sence</p>', '2018-03-04 18:55:41', '2018-03-04 18:55:41', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a36633736356161312d653436382d343261322d613738342d6165383434303537313333340d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830333034543138353534310d0a4454454e443a3230313830333034543138353534310d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a4372c3a9657220c3a020706172746972206465206c612076756520c3a9636f6c652064752064696d616e6368650d0a53554d4d4152593a30202d3320616e732032362f30322f323031380d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x36633736356161312d653436382d343261322d613738342d616538343430353731333334, 2, 1525635356, 0x6434623833346637376436386230393563316431653933323166363037303466, 421, 0x564556454e54, 0x36633736356161312d653436382d343261322d613738342d616538343430353731333334, ''),
(87, 2, '0 -3 ans 25/04/2018', 'Créer à partir de la vue école du dimanche', '<p>Pr&eacute;sence</p>', '2018-04-25 20:30:17', '2018-04-25 20:30:17', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a65313365626135622d313836342d343433362d393138622d3832643963643039366434660d0a44545354414d503a3230313830353036543231333535360d0a435245415445443a3230313830353036543231333535360d0a445453544152543a3230313830343235543230333031370d0a4454454e443a3230313830343235543230333031370d0a4c4153542d4d4f4449464945443a3230313830353036543231333535360d0a4445534352495054494f4e3a4372c3a9657220c3a020706172746972206465206c612076756520c3a9636f6c652064752064696d616e6368650d0a53554d4d4152593a30202d3320616e732032352f30342f323031380d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x65313365626135622d313836342d343433362d393138622d383264396364303936643466, 2, 1525635356, 0x3762616265333866613865353039303739316431313535663737326233613163, 421, 0x564556454e54, 0x65313365626135622d313836342d343433362d393138622d383264396364303936643466, ''),
(96, 2, 'essai calendrier perso', 'coucou', NULL, '2018-05-15 00:00:00', '2018-05-15 13:30:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a41463237454230412d304144452d343042322d394638372d3535443630374433383332420d0a44545354414d503a3230313830353036543231333634390d0a435245415445443a3230313830353036543231333634390d0a4445534352495054494f4e3a636f75636f750d0a53554d4d4152593a65737361692063616c656e647269657220706572736f0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a445453544152543a3230313830353135543030303030300d0a4454454e443a3230313830353135543133333030300d0a4c4153542d4d4f4449464945443a3230313830353039543039333435360d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x41463237454230412d304144452d343042322d394638372d353544363037443338333242, 7, 1525851296, 0x6164373730383765663765393337656261646637333863333463613436363830, 385, 0x564556454e54, 0x41463237454230412d304144452d343042322d394638372d353544363037443338333242, ''),
(97, 2, 'Un essai pour la classe test', '', NULL, '2018-05-06 00:00:00', '2018-05-07 00:00:00', 0, 'Ecole du dimanche', 8, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a44334441374243322d323436352d343930432d393945342d3839313142464239333341390d0a44545354414d503a3230313830353036543231333931330d0a435245415445443a3230313830353036543231333931330d0a4445534352495054494f4e3a0d0a53554d4d4152593a556e20657373616920706f7572206c6120636c6173736520746573740d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a445453544152543a3230313830353036543030303030300d0a4454454e443a3230313830353037543030303030300d0a4c4153542d4d4f4449464945443a3230313830353036543231333933310d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x44334441374243322d323436352d343930432d393945342d383931314246423933334139, 6, 1525635571, 0x6566303665303264653331353761366532633236623938313165363661356361, 385, 0x564556454e54, 0x44334441374243322d323436352d343930432d393945342d383931314246423933334139, ''),
(99, 2, 'événement récurrent', '', '', '2018-05-15 00:00:00', '2018-05-15 11:30:00', 0, 'Ecole du dimanche', 8, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a44453044434135312d443136352d343132432d394645322d4138384441443246363633380d0a44545354414d503a3230313830353036543231343235350d0a435245415445443a3230313830353036543231343235350d0a4445534352495054494f4e3a0d0a53554d4d4152593ac3a976c3a96e656d656e742072c3a963757272656e740d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a445453544152543a3230313830353135543030303030300d0a4454454e443a3230313830353135543131333030300d0a4c4153542d4d4f4449464945443a3230313830353135543132303533300d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, 6, 1526378730, 0x3833393466316264616462653532316535643738383533356630623535363365, 379, 0x564556454e54, 0x44453044434135312d443136352d343132432d394645322d413838444144324636363338, ''),
(101, 2, 'événement récurrent', 'coucou', NULL, '2018-05-11 00:00:00', '2018-05-12 00:00:00', 0, 'Ecole du dimanche', 8, '2018-05-17 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a36323641443036442d304145302d343344452d393931422d3535383545453745433841460d0a44545354414d503a3230313830353036543231353933300d0a435245415445443a3230313830353036543231353933300d0a4445534352495054494f4e3a636f75636f750d0a53554d4d4152593ac3a976c3a96e656d656e742072c3a963757272656e740d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a4558444154453a3230313830353133543030303030300d0a4558444154453a3230313830353132543030303030300d0a4558444154453a3230313830353132543030303030300d0a4558444154453a3230313830353134543030303030300d0a5252554c453a465245513d4441494c593b554e54494c3d3230313830353136543030303030300d0a445453544152543a3230313830353131543030303030300d0a4454454e443a3230313830353132543030303030300d0a4c4153542d4d4f4449464945443a3230313830353330543232303430330d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, 6, 1527710643, 0x6166396465376566623863613535656464303739643134366432343131306634, 521, 0x564556454e54, 0x36323641443036442d304145302d343344452d393931422d353538354545374543384146, ''),
(102, 2, 'essai de 0 à 3 ans mensuel', '', NULL, '2018-05-03 09:00:00', '2018-05-03 14:30:00', 0, 'Ecole du dimanche', 2, '2018-05-03 14:30:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a43333342354142332d333232432d344531322d384137342d3241353846344244384430440d0a44545354414d503a3230313830353036543232303130360d0a435245415445443a3230313830353036543232303130360d0a4445534352495054494f4e3a0d0a53554d4d4152593a6573736169206465203020c3a0203320616e73206d656e7375656c0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a5252554c453a465245513d4d4f4e54484c593b554e54494c3d3230313830373233543039303030300d0a445453544152543a3230313830353033543039303030300d0a4454454e443a3230313830353033543134333030300d0a4558444154453a3230313830373033543039303030300d0a4558444154453a3230313830363033543039303030300d0a4c4153542d4d4f4449464945443a3230313830363135543037323435350d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, 2, 1529040295, 0x3336643961643865346262656264653366613333303538643165386165393635, 474, 0x564556454e54, 0x43333342354142332d333232432d344531322d384137342d324135384634424438443044, ''),
(105, 2, 'Par trimestre', 'juste pour tester', '<p>coucou</p>', '2018-08-14 00:00:00', '2018-08-15 00:00:00', 0, 'Ecole du dimanche', 4, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a37444139334336462d303335352d343045392d384246372d4432444545414634443846320d0a44545354414d503a3230313830353038543131343130330d0a435245415445443a3230313830353038543131343130330d0a4445534352495054494f4e3a6a7573746520706f7572207465737465720d0a53554d4d4152593a506172207472696d65737472650d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a445453544152543a3230313830383134543030303030300d0a4454454e443a3230313830383135543030303030300d0a4c4153542d4d4f4449464945443a3230313830383235543230353231370d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, 4, 1535223137, 0x6535643639393930353430303866636335666433636130343032346333646532, 387, 0x564556454e54, 0x37444139334336462d303335352d343045392d384246372d443244454541463444384632, ''),
(109, 2, 'événement récurrent modifié', 'coucou', '', '2018-05-31 00:00:00', '2018-06-01 00:00:00', 0, 'Ecole du dimanche', 8, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a30383142324644332d304339422d343945382d384631352d4539393846414241394439390d0a44545354414d503a3230313830353134543138313633300d0a435245415445443a3230313830353134543138313633300d0a445453544152543a3230313830353331543030303030300d0a4454454e443a3230313830363031543030303030300d0a4c4153542d4d4f4449464945443a3230313830353134543138313633300d0a4445534352495054494f4e3a636f75636f750d0a53554d4d4152593ac3a976c3a96e656d656e742072c3a963757272656e74206d6f64696669c3a90d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x30383142324644332d304339422d343945382d384631352d453939384641424139443939, 6, 1526314590, 0x3733323664323730333866326236613034643533666163376636316134626630, 394, 0x564556454e54, 0x30383142324644332d304339422d343945382d384631352d453939384641424139443939, ''),
(110, 2, 'modifié', 'coucou', '', '2018-05-18 00:00:00', '2018-05-19 00:00:00', 0, 'Ecole du dimanche', 8, '0000-00-00 00:00:00', NULL, 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a46383837443637442d433638442d343241362d423643322d3230413446414245303439360d0a44545354414d503a3230313830353134543138313730390d0a435245415445443a3230313830353134543138313730390d0a445453544152543a3230313830353138543030303030300d0a4454454e443a3230313830353139543030303030300d0a4c4153542d4d4f4449464945443a3230313830353134543138313730390d0a4445534352495054494f4e3a636f75636f750d0a53554d4d4152593a6d6f64696669c3a90d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x46383837443637442d433638442d343241362d423643322d323041344641424530343936, 6, 1526314629, 0x3563643033323130613333363737393966656264323932313836643738303737, 371, 0x564556454e54, 0x46383837443637442d433638442d343241362d423643322d323041344641424530343936, ''),
(126, 2, 'essai de 0 à 3 ans mensuel', 'Voici un bel événement', '', '2018-06-03 09:00:00', '2018-06-03 14:30:00', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', '1 avenue de Europe Strasbourg, 67000 France', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a43314337434442392d444543352d344638362d393141362d3634373038384342343632330d0a44545354414d503a3230313830363135543037343735330d0a435245415445443a3230313830363135543037343735330d0a445453544152543a3230313830363033543039303030300d0a4454454e443a3230313830363033543134333030300d0a4c4153542d4d4f4449464945443a3230313830363135543037343735330d0a4445534352495054494f4e3a566f69636920756e2062656c20c3a976c3a96e656d656e740d0a53554d4d4152593a6573736169206465203020c3a0203320616e73206d656e7375656c0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d2231204156454e5545204445204555524f5045205354524153424f5552472c203637303030204652414e4345223a67656f3a34382e3539353730333320636f6d6d61474d41500d0a2020372e373734333931320d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x43314337434442392d444543352d344638362d393141362d363437303838434234363233, 2, 1529041673, 0x3835376438313266353761333634646531393536383530656363613366653361, 619, 0x564556454e54, 0x43314337434442392d444543352d344638362d393141362d363437303838434234363233, '48.5957033 commaGMAP 7.7743912'),
(128, 2, 'localisation', 'Description', '<p>essai</p>', '2018-06-07 00:00:00', '2018-06-08 00:00:00', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', '30 rue des Lilas Illkirch', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a33363335383246392d443939422d343042342d413339462d4636423943424643423431440d0a44545354414d503a3230313830363137543132343635380d0a435245415445443a3230313830363137543132343635380d0a445453544152543a3230313830363037543030303030300d0a4454454e443a3230313830363038543030303030300d0a4c4153542d4d4f4449464945443a3230313830363137543132343635380d0a4445534352495054494f4e3a4465736372697074696f6e0d0a53554d4d4152593a6c6f63616c69736174696f6e0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d2233302052554520444553204c494c415320494c4c4b49524348223a67656f3a34382e353432363135392c372e3733303839310d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x33363335383246392d443939422d343042342d413339462d463642394342464342343144, 2, 1529232418, 0x6132333832333339343236646166323832346637306135363537373266376664, 559, 0x564556454e54, 0x33363335383246392d443939422d343042342d413339462d463642394342464342343144, '48.5426159 commaGMAP 7.730891'),
(129, 2, 'essai de 0 à 3 ans mensuel', '', '', '2018-06-13 09:00:00', '2018-06-13 14:30:00', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', '1 rue Robert Kieffer Bischheim', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e352f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a46374445413739422d303741382d343243302d394430392d3746444346323835413146420d0a44545354414d503a3230313830363137543132343730340d0a435245415445443a3230313830363137543132343730340d0a445453544152543a3230313830363133543039303030300d0a4454454e443a3230313830363133543134333030300d0a4c4153542d4d4f4449464945443a3230313830363137543132343730340d0a4445534352495054494f4e3a0d0a53554d4d4152593a6573736169206465203020c3a0203320616e73206d656e7375656c0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d22312052554520524f42455254204b4945464645522042495343484845494d223a67656f3a0d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x46374445413739422d303741382d343243302d394430392d374644434632383541314642, 2, 1529232424, 0x6436616266393237393630656237306266653430383632343732636566623238, 549, 0x564556454e54, 0x46374445413739422d303741382d343243302d394430392d374644434632383541314642, ''),
(130, 2, 'test', 'test', '', '2018-09-05 00:00:00', '2018-09-06 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', 'test', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a33353635363336342d363933342d343942352d393842452d4538304535383746314541370d0a44545354414d503a3230313830383136543233323131310d0a435245415445443a3230313830383136543233323131310d0a4445534352495054494f4e3a746573740d0a53554d4d4152593a746573740d0a4c4f434154494f4e3a746573740d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d544553543a67656f3a35312e313537363636315c2c2d312e343435383537320d0a445453544152543a3230313830393035543030303030300d0a4454454e443a3230313830393036543030303030300d0a4c4153542d4d4f4449464945443a3230313830383136543233323133330d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x33353635363336342d363933342d343942352d393842452d453830453538374631454137, 7, 1534454493, 0x6562623266376235666534353932653238333632333863666666333364646337, 524, 0x564556454e54, 0x33353635363336342d363933342d343942352d393842452d453830453538374631454137, '51.1576661 commaGMAP -1.4458572'),
(131, 1, 'Culte 1', 'Premier culte', '', '2018-09-14 00:00:00', '2018-09-15 00:00:00', 0, 'Service d\'église', 4, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a41343244353634332d333934442d344644352d424439452d4338354446343532373635350d0a44545354414d503a3230313830393134543134343334330d0a435245415445443a3230313830393134543134343334330d0a445453544152543a3230313830393134543030303030300d0a4454454e443a3230313830393135543030303030300d0a4c4153542d4d4f4449464945443a3230313830393134543134343334330d0a4445534352495054494f4e3a5072656d6965722063756c74650d0a53554d4d4152593a43756c746520310d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d22223a67656f3a0d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x41343244353634332d333934442d344644352d424439452d433835444634353237363535, 4, 1536929023, 0x6237643135386166396535353338643665323232356265326238306166663932, 508, 0x564556454e54, 0x41343244353634332d333934442d344644352d424439452d433835444634353237363535, ''),
(132, 2, 'cucou', '', '', '2018-10-01 00:00:00', '2018-10-02 00:00:00', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a32433338463232462d433243392d343638332d384541362d3734364136314243343442330d0a44545354414d503a3230313831303031543230353835370d0a435245415445443a3230313831303031543230353835370d0a4445534352495054494f4e3a0d0a53554d4d4152593a6375636f750d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303031543030303030300d0a4454454e443a3230313831303032543030303030300d0a4c4153542d4d4f4449464945443a3230313831303031543232353134380d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x32433338463232462d433243392d343638332d384541362d373436413631424334344233, 2, 1538427108, 0x3266303031666237373131623330613234616566303530383730386537306434, 491, 0x564556454e54, 0x32433338463232462d433243392d343638332d384541362d373436413631424334344233, ''),
(134, 2, 'mon essai', '', '', '2018-10-03 00:00:00', '2018-10-06 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a38333937393138442d333936362d344435302d393941462d4234394437314435413741450d0a44545354414d503a3230313831303031543232323034330d0a435245415445443a3230313831303031543232323034330d0a4445534352495054494f4e3a0d0a53554d4d4152593a6d6f6e2065737361690d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303033543030303030300d0a4454454e443a3230313831303036543030303030300d0a4c4153542d4d4f4449464945443a3230313831303032543132313231320d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x38333937393138442d333936362d344435302d393941462d423439443731443541374145, 7, 1538475132, 0x3466346430393638376366623863353262353337306331653534623565363733, 495, 0x564556454e54, 0x38333937393138442d333936362d344435302d393941462d423439443731443541374145, ''),
(135, 2, 'Mon essai', '', '', '2018-10-17 00:00:00', '2018-10-19 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a32314546343035442d393639432d344434342d384543382d3242414330324231413643420d0a44545354414d503a3230313831303031543232323332390d0a435245415445443a3230313831303031543232323332390d0a4445534352495054494f4e3a0d0a53554d4d4152593a4d6f6e2065737361690d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303137543030303030300d0a4454454e443a3230313831303139543030303030300d0a4c4153542d4d4f4449464945443a3230313831303130543233303330310d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, 8, 1539205381, 0x3331613166636333613430316237343263656261376638326466613233333365, 495, 0x564556454e54, 0x32314546343035442d393639432d344434342d384543382d324241433032423141364342, ''),
(136, 2, 'Mon essai2', '', '', '2018-10-30 00:00:00', '2018-11-02 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a32334143353233452d383136392d343038322d383339432d4446353038314431454132300d0a44545354414d503a3230313831303031543232323431380d0a435245415445443a3230313831303031543232323431380d0a4445534352495054494f4e3a0d0a53554d4d4152593a4d6f6e206573736169320d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303330543030303030300d0a4454454e443a3230313831313032543030303030300d0a4c4153542d4d4f4449464945443a3230313831303031543232323531330d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x32334143353233452d383136392d343038322d383339432d444635303831443145413230, 8, 1538425513, 0x6334383336626232623039613766393835343031643733633732633064636464, 496, 0x564556454e54, 0x32334143353233452d383136392d343038322d383339432d444635303831443145413230, ''),
(137, 2, 'toto modifié', '', '', '2018-10-02 00:00:00', '2018-10-03 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a42463838323634342d393736332d344638452d393238412d4635374432323932453344350d0a44545354414d503a3230313831303031543232353235370d0a435245415445443a3230313831303031543232353235370d0a4445534352495054494f4e3a0d0a53554d4d4152593a746f746f206d6f64696669c3a90d0a53455155454e43453a300d0a4c4f434154494f4e3a0d0a5452414e53503a4f50415155450d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303032543030303030300d0a4454454e443a3230313831303033543030303030300d0a4c4153542d4d4f4449464945443a3230313831303032543132313230390d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x42463838323634342d393736332d344638452d393238412d463537443232393245334435, 7, 1538475129, 0x3261306531313464356264333262623739383735313961383031353035643664, 514, 0x564556454e54, 0x42463838323634342d393736332d344638452d393238412d463537443232393245334435, ''),
(138, 2, 'réc', '', '', '2018-10-22 00:00:00', '2018-10-23 00:00:00', 0, 'Ecole du dimanche', 2, '2018-10-26 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a39373342453637372d453634352d343030332d414530442d4332343836434537384532420d0a44545354414d503a3230313831303031543232353631300d0a435245415445443a3230313831303031543232353631300d0a4445534352495054494f4e3a0d0a53554d4d4152593a72c3a9630d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a5452414e53503a4f50415155450d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a4558444154453a3230313831303233543030303030300d0a5252554c453a465245513d4441494c593b554e54494c3d3230313831303235543030303030300d0a445453544152543a3230313831303232543030303030300d0a4454454e443a3230313831303233543030303030300d0a4c4153542d4d4f4449464945443a3230313831303031543232353730360d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x39373342453637372d453634352d343030332d414530442d433234383643453738453242, 2, 1538427426, 0x3932623764633935636662333035316564636337613738383530316239623334, 569, 0x564556454e54, 0x39373342453637372d453634352d343030332d414530442d433234383643453738453242, ''),
(139, 2, 'Reservation for Kids', '', '', '2018-10-13 00:00:00', '2018-10-14 00:00:00', 0, 'Ecole du dimanche', 0, '0000-00-00 00:00:00', '', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a36343345433337422d423242462d344346392d424237302d4442303645323139323534350d0a44545354414d503a3230313831303130543233303831320d0a435245415445443a3230313831303130543233303831320d0a4445534352495054494f4e3a0d0a53554d4d4152593a5265736572766174696f6e20666f72204b6964730d0a4c4f434154494f4e3a0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d3a67656f3a0d0a445453544152543a3230313831303133543030303030300d0a4454454e443a3230313831303134543030303030300d0a4c4153542d4d4f4449464945443a3230313831303133543233303334390d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x36343345433337422d423242462d344346392d424237302d444230364532313932353435, 10, 1539464629, 0x6362353261313230326165613663323839613964393936303666323638383934, 506, 0x564556454e54, 0x36343345433337422d423242462d344346392d424237302d444230364532313932353435, ''),
(140, 2, 'Mon essai2', '', '', '2018-10-09 00:00:00', '2018-10-10 00:00:00', 0, 'Ecole du dimanche', 2, '0000-00-00 00:00:00', '1 rue Robert Kieffer 67800 Bischheim', 0x424547494e3a5643414c454e4441520d0a56455253494f4e3a322e300d0a50524f4449443a2d2f2f4563636c6573696143524d2e2f2f20564f626a65637420342e312e362f2f454e0d0a43414c5343414c453a475245474f5249414e0d0a424547494e3a564556454e540d0a5549443a46314439443632462d333137362d344331332d383543372d4635363834333646393645300d0a44545354414d503a3230313831303133543038313135390d0a435245415445443a3230313831303133543038313135390d0a445453544152543a3230313831303039543030303030300d0a4454454e443a3230313831303130543030303030300d0a4c4153542d4d4f4449464945443a3230313831303133543038313135390d0a4445534352495054494f4e3a0d0a53554d4d4152593a4d6f6e206573736169320d0a4c4f434154494f4e3a312072756520526f62657274204b6965666665722036373830302042697363686865696d0d0a53455155454e43453a300d0a582d4150504c452d54524156454c2d41445649534f52592d4245484156494f523a4155544f4d415449430d0a582d4150504c452d535452554354555245442d4c4f434154494f4e3b56414c55453d5552493b582d4150504c452d5241444955533d34392e39313330373538373032393638363b582d54490d0a20544c453d22312052554520524f42455254204b4945464645522036373830302042495343484845494d223a67656f3a34382e363136333738322c372e373532380d0a203136390d0a454e443a564556454e540d0a454e443a5643414c454e4441520d0a, 0x46314439443632462d333137362d344331332d383543372d463536383433364639364530, 2, 1539411119, 0x6637633936623062326666626363363161656364303539373765333035653330, 593, 0x564556454e54, 0x46314439443632462d333137362d344331332d383543372d463536383433364639364530, '48.6163782 commaGMAP 7.7528169');

-- --------------------------------------------------------

--
-- Structure de la table `event_attend`
--

CREATE TABLE `event_attend` (
  `attend_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `person_id` int(11) NOT NULL DEFAULT 0,
  `checkin_date` datetime DEFAULT NULL,
  `checkin_id` int(11) DEFAULT NULL,
  `checkout_date` datetime DEFAULT NULL,
  `checkout_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `event_attend`
--

INSERT INTO `event_attend` (`attend_id`, `event_id`, `person_id`, `checkin_date`, `checkin_id`, `checkout_date`, `checkout_id`) VALUES
(50, 56, 6, '2018-02-17 22:20:38', 1, '2018-02-17 22:20:38', 1),
(51, 56, 12, '2018-02-17 22:20:38', 1, '2018-02-17 22:20:38', 1),
(52, 57, 6, '2018-02-18 11:51:53', 1, '2018-02-18 11:51:53', 1),
(53, 57, 12, '2018-02-18 11:51:53', 1, NULL, 1),
(54, 86, 6, '2018-02-26 18:55:41', 1, '2018-02-26 18:55:41', 1),
(55, 86, 12, '2018-02-26 18:55:41', 1, '2018-02-26 18:55:41', 1),
(60, 87, 6, '2018-04-25 20:30:17', 1, '2018-04-25 20:30:17', 1),
(61, 87, 12, '2018-04-25 20:30:17', 1, NULL, 1),
(62, 132, 6, '2018-10-01 20:58:57', 1, NULL, NULL),
(63, 132, 9, '2018-10-01 20:58:57', 1, NULL, NULL),
(64, 132, 12, '2018-10-01 20:58:57', 1, NULL, NULL),
(65, 138, 6, '2018-10-01 22:56:10', 1, NULL, NULL),
(66, 138, 9, '2018-10-01 22:56:10', 1, NULL, NULL),
(67, 138, 12, '2018-10-01 22:56:10', 1, NULL, NULL),
(68, 140, 6, '2018-10-13 08:11:59', 1, NULL, NULL),
(69, 140, 9, '2018-10-13 08:11:59', 1, NULL, NULL),
(70, 140, 12, '2018-10-13 08:11:59', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `event_types`
--

CREATE TABLE `event_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type_defstarttime` time NOT NULL DEFAULT '00:00:00',
  `type_defrecurtype` enum('none','weekly','monthly','yearly') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `type_defrecurDOW` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Sunday',
  `type_defrecurDOM` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `type_defrecurDOY` date NOT NULL DEFAULT '2000-01-01',
  `type_active` int(1) NOT NULL DEFAULT 1,
  `type_grpid` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `event_types`
--

INSERT INTO `event_types` (`type_id`, `type_name`, `type_defstarttime`, `type_defrecurtype`, `type_defrecurDOW`, `type_defrecurDOM`, `type_defrecurDOY`, `type_active`, `type_grpid`) VALUES
(1, 'Service d\'église', '10:30:00', 'weekly', 'Sunday', '', '2016-01-01', 1, NULL),
(2, 'Ecole du dimanche', '09:30:00', 'weekly', 'Sunday', '', '2016-01-01', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `family_custom`
--

CREATE TABLE `family_custom` (
  `fam_ID` mediumint(9) NOT NULL DEFAULT 0,
  `c1` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `family_custom`
--

INSERT INTO `family_custom` (`fam_ID`, `c1`) VALUES
(8, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `family_custom_master`
--

CREATE TABLE `family_custom_master` (
  `fam_custom_Order` smallint(6) NOT NULL DEFAULT 0,
  `fam_custom_Field` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fam_custom_Name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fam_custom_Special` mediumint(8) UNSIGNED DEFAULT NULL,
  `fam_custom_Side` enum('left','right') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'left',
  `fam_custom_FieldSec` tinyint(4) NOT NULL DEFAULT 1,
  `type_ID` tinyint(4) NOT NULL DEFAULT 0,
  `family_custom_id` mediumint(9) UNSIGNED NOT NULL,
  `fam_custom_comment` text COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'comment for GDPR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `family_custom_master`
--

INSERT INTO `family_custom_master` (`fam_custom_Order`, `fam_custom_Field`, `fam_custom_Name`, `fam_custom_Special`, `fam_custom_Side`, `fam_custom_FieldSec`, `type_ID`, `family_custom_id`, `fam_custom_comment`) VALUES
(1, 'c1', 'eee', 2, 'left', 1, 9, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `family_fam`
--

CREATE TABLE `family_fam` (
  `fam_ID` mediumint(9) UNSIGNED NOT NULL,
  `fam_Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_Address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_Address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_Zip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_HomePhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_WorkPhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_CellPhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_WeddingDate` date DEFAULT NULL,
  `fam_DateEntered` datetime NOT NULL,
  `fam_DateLastEdited` datetime DEFAULT NULL,
  `fam_EnteredBy` smallint(5) NOT NULL DEFAULT 0,
  `fam_EditedBy` smallint(5) UNSIGNED DEFAULT 0,
  `fam_scanCheck` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_scanCredit` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `fam_SendNewsLetter` enum('FALSE','TRUE') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FALSE',
  `fam_DateDeactivated` date DEFAULT NULL,
  `fam_OkToCanvass` enum('FALSE','TRUE') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FALSE',
  `fam_Canvasser` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `fam_Latitude` double DEFAULT NULL,
  `fam_Longitude` double DEFAULT NULL,
  `fam_Envelope` mediumint(9) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `family_fam`
--

INSERT INTO `family_fam` (`fam_ID`, `fam_Name`, `fam_Address1`, `fam_Address2`, `fam_City`, `fam_State`, `fam_Zip`, `fam_Country`, `fam_HomePhone`, `fam_WorkPhone`, `fam_CellPhone`, `fam_Email`, `fam_WeddingDate`, `fam_DateEntered`, `fam_DateLastEdited`, `fam_EnteredBy`, `fam_EditedBy`, `fam_scanCheck`, `fam_scanCredit`, `fam_SendNewsLetter`, `fam_DateDeactivated`, `fam_OkToCanvass`, `fam_Canvasser`, `fam_Latitude`, `fam_Longitude`, `fam_Envelope`) VALUES
(1, 'Alpha', '12 avenue de Europe', '', 'Strasbourg', '', '67000', 'France', '', '', '', '', NULL, '2018-10-13 06:35:42', '2018-01-14 12:45:52', 1, 1, NULL, NULL, 'TRUE', NULL, 'FALSE', 0, 48.5954424, 7.7741715, 2),
(2, 'Beta', '10 rue de la ville', '', 'Souffelweyersheim', '', '67460', 'France', '', '', '', '', NULL, '2018-01-10 22:02:14', NULL, 1, 0, NULL, NULL, 'TRUE', NULL, 'TRUE', 0, 48.6341909, 7.7419621, 3),
(3, 'Alexander', '', '', '', '', '', '', '', '', '', 'rohan.alex@virgin.net', NULL, '2018-09-05 19:39:42', NULL, 1, 0, NULL, NULL, 'FALSE', NULL, 'FALSE', 0, NULL, NULL, 1),
(4, 'Seul', '1 rue des tartares', '', 'Paris', '', '75000', 'France', '', '', '', '', NULL, '2018-03-12 10:54:13', NULL, 1, 0, NULL, NULL, 'FALSE', NULL, 'FALSE', 0, NULL, NULL, 0),
(5, 'seul', '1 rue Tartanpoin', '', '', '', '', 'France', '', '', '', '', NULL, '2018-04-02 22:59:25', NULL, 1, 0, NULL, NULL, 'FALSE', NULL, 'FALSE', 0, NULL, NULL, 0),
(6, 'Essai', '1 rue robert kieffer', '', 'Bisccheim', '', '67800', 'France', '', '', '', '', NULL, '2018-05-09 09:28:11', NULL, 1, 0, NULL, NULL, 'FALSE', NULL, 'FALSE', 0, NULL, NULL, 0),
(8, 'One Person', '1 rue Robert Kieffer', '', 'Bischheim', '', '67800', 'France', '', '', '', '', NULL, '2018-10-14 08:38:49', NULL, 1, 0, NULL, NULL, 'TRUE', NULL, 'TRUE', 0, 48.6163782, 7.7528169, 0);

-- --------------------------------------------------------

--
-- Structure de la table `fundraiser_fr`
--

CREATE TABLE `fundraiser_fr` (
  `fr_ID` mediumint(9) UNSIGNED NOT NULL,
  `fr_date` date DEFAULT NULL,
  `fr_title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `fr_description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `fr_EnteredBy` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `fr_EnteredDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `gdpr_infos`
--

CREATE TABLE `gdpr_infos` (
  `gdpr_info_id` mediumint(9) UNSIGNED NOT NULL,
  `gdpr_info_About` enum('Person','Family') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Person',
  `gdpr_info_Name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gdpr_info_Type` tinyint(4) NOT NULL DEFAULT 0,
  `gdpr_info_comment` text COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'comment for GDPR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `gdpr_infos`
--

INSERT INTO `gdpr_infos` (`gdpr_info_id`, `gdpr_info_About`, `gdpr_info_Name`, `gdpr_info_Type`, `gdpr_info_comment`) VALUES
(1, 'Person', 'Gender', 3, ''),
(2, 'Person', 'Title', 3, ''),
(3, 'Person', 'First Name', 3, ''),
(4, 'Person', 'Middle Name', 3, ''),
(5, 'Person', 'Last Name', 3, ''),
(7, 'Person', 'Birth Month', 12, ''),
(8, 'Person', 'Birth Day', 12, ''),
(9, 'Person', 'Birth Year', 6, ''),
(10, 'Person', 'Suffix', 3, ''),
(11, 'Person', 'Hide Age', 3, ''),
(12, 'Family', 'Role', 12, ''),
(13, 'Person', 'Home Phone', 3, ''),
(14, 'Person', 'Work Phone', 3, ''),
(15, 'Person', 'Mobile Phone', 3, ''),
(16, 'Person', 'Email', 3, ''),
(17, 'Person', 'Work / Other Email', 3, ''),
(18, 'Person', 'Facebook ID', 3, ''),
(19, 'Person', 'Twitter', 3, ''),
(21, 'Person', 'LinkedIn', 3, ''),
(22, 'Person', 'Classification', 12, ''),
(23, 'Person', 'Membership Date', 2, ''),
(24, 'Person', 'Friend Date', 2, ''),
(25, 'Family', 'Family Name', 3, ''),
(26, 'Family', 'Address 1', 4, 'Ah le RGPD c\'est génial'),
(27, 'Family', 'Address 2', 4, ''),
(28, 'Family', 'City', 3, ''),
(29, 'Family', 'Country', 12, ''),
(30, 'Family', 'State', 3, ''),
(33, 'Family', 'Home Phone', 3, ''),
(34, 'Family', 'Work Phone', 3, ''),
(35, 'Family', 'Mobile Phone', 3, ''),
(36, 'Family', 'Email', 3, ''),
(37, 'Family', 'Send Newsletter', 1, ''),
(39, 'Family', 'Wedding Date', 2, '');

-- --------------------------------------------------------

--
-- Structure de la table `groupmembers`
--

CREATE TABLE `groupmembers` (
  `id` int(10) UNSIGNED NOT NULL,
  `principal_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupprop_4`
--

CREATE TABLE `groupprop_4` (
  `per_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `c1` varchar(50) DEFAULT NULL,
  `c2` enum('false','true') DEFAULT NULL,
  `c3` date DEFAULT NULL,
  `c4` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `groupprop_master`
--

CREATE TABLE `groupprop_master` (
  `grp_ID` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `prop_ID` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `prop_Field` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `prop_Name` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prop_Description` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_ID` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `prop_Special` mediumint(9) UNSIGNED DEFAULT NULL,
  `prop_PersonDisplay` enum('false','true') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'false',
  `grp_mster_id` mediumint(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Group-specific properties order, name, description, type';

--
-- Déchargement des données de la table `groupprop_master`
--

INSERT INTO `groupprop_master` (`grp_ID`, `prop_ID`, `prop_Field`, `prop_Name`, `prop_Description`, `type_ID`, `prop_Special`, `prop_PersonDisplay`, `grp_mster_id`) VALUES
(4, 1, 'c1', 'J\'emmène quoi ?', '', 3, NULL, 'true', 7),
(4, 2, 'c2', 'serait présent', '', 1, NULL, 'true', 8),
(4, 3, 'c3', 'Date repas partage', '', 2, NULL, 'true', 9),
(4, 4, 'c4', 'Responsable', '4', 9, 4, 'false', 11);

-- --------------------------------------------------------

--
-- Structure de la table `group_grp`
--

CREATE TABLE `group_grp` (
  `grp_ID` mediumint(8) UNSIGNED NOT NULL,
  `grp_Type` tinyint(4) NOT NULL DEFAULT 0,
  `grp_RoleListID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `grp_DefaultRole` mediumint(9) NOT NULL DEFAULT 0,
  `grp_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `grp_Description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `grp_hasSpecialProps` tinyint(1) NOT NULL DEFAULT 0,
  `grp_active` tinyint(1) NOT NULL DEFAULT 1,
  `grp_include_email_export` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `group_grp`
--

INSERT INTO `group_grp` (`grp_ID`, `grp_Type`, `grp_RoleListID`, `grp_DefaultRole`, `grp_Name`, `grp_Description`, `grp_hasSpecialProps`, `grp_active`, `grp_include_email_export`) VALUES
(2, 4, 14, 2, '0 -3 ans', '', 0, 1, 1),
(3, 5, 15, 1, 'Groupe de maison Strasbourg centre', '', 0, 1, 1),
(4, 1, 16, 1, 'Culte', 'Les cultes du dimanche', 1, 1, 1),
(7, 0, 17, 1, 'Conseil spirituel', '', 0, 1, 1),
(8, 4, 19, 2, 'class test', '', 0, 1, 1),
(9, 0, 20, 1, 'From EcclesiaCRM3 without Group', NULL, 0, 1, 1),
(10, 0, 21, 1, 'test', NULL, 0, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `group_manager_person`
--

CREATE TABLE `group_manager_person` (
  `grp_mgr_per_id` mediumint(9) UNSIGNED NOT NULL,
  `grp_mgr_per_person_ID` mediumint(9) UNSIGNED NOT NULL,
  `grp_mgr_per_group_ID` mediumint(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `group_manager_person`
--

INSERT INTO `group_manager_person` (`grp_mgr_per_id`, `grp_mgr_per_person_ID`, `grp_mgr_per_group_ID`) VALUES
(1, 18, 10);

-- --------------------------------------------------------

--
-- Structure de la table `istlookup_lu`
--

CREATE TABLE `istlookup_lu` (
  `lu_fam_ID` mediumint(9) NOT NULL DEFAULT 0,
  `lu_LookupDateTime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `lu_DeliveryLine1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_DeliveryLine2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_ZipAddon` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_Zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_Addon` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_LOTNumber` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_DPCCheckdigit` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_RecordType` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_LastLine` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_CarrierRoute` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_ReturnCodes` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_ErrorCodes` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lu_ErrorDesc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='US Address Verification Lookups From Intelligent Search Tech';

-- --------------------------------------------------------

--
-- Structure de la table `kioskassginment_kasm`
--

CREATE TABLE `kioskassginment_kasm` (
  `kasm_ID` mediumint(9) UNSIGNED NOT NULL,
  `kasm_kdevId` mediumint(9) DEFAULT NULL,
  `kasm_AssignmentType` mediumint(9) DEFAULT NULL,
  `kasm_EventId` mediumint(9) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kioskdevice_kdev`
--

CREATE TABLE `kioskdevice_kdev` (
  `kdev_ID` mediumint(9) UNSIGNED NOT NULL,
  `kdev_GUIDHash` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kdev_Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kdev_deviceType` mediumint(9) NOT NULL DEFAULT 0,
  `kdev_lastHeartbeat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `kdev_Accepted` tinyint(1) DEFAULT NULL,
  `kdev_PendingCommands` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `list_icon`
--

CREATE TABLE `list_icon` (
  `lst_ic_id` mediumint(9) UNSIGNED NOT NULL,
  `lst_ic_lst_ID` mediumint(9) UNSIGNED NOT NULL,
  `lst_ic_lst_Option_ID` mediumint(9) UNSIGNED NOT NULL,
  `lst_ic_lst_url` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lst_ic_only_person_View` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `list_icon`
--

INSERT INTO `list_icon` (`lst_ic_id`, `lst_ic_lst_ID`, `lst_ic_lst_Option_ID`, `lst_ic_lst_url`, `lst_ic_only_person_View`) VALUES
(7, 1, 2, 'gm-green-dot.png', 0),
(10, 1, 5, 'gm-blue.png', 0),
(11, 1, 4, 'gm-green.png', 0),
(14, 1, 1, 'gm-orange-dot.png', 0),
(15, 1, 3, 'gm-purple-pushpin.png', 0);

-- --------------------------------------------------------

--
-- Structure de la table `list_lst`
--

CREATE TABLE `list_lst` (
  `lst_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `lst_OptionID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `lst_OptionSequence` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `lst_OptionName` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `list_lst`
--

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_OptionName`) VALUES
(1, 1, 1, 'Membre'),
(1, 2, 2, 'Regular Attender'),
(1, 3, 3, 'Invité'),
(1, 5, 4, 'Non-Attender'),
(1, 4, 5, 'Non-Attender (staff)'),
(2, 1, 1, 'Chef de famille'),
(2, 2, 2, 'Epouse'),
(2, 3, 3, 'Enfant'),
(2, 4, 4, 'Autre relation'),
(2, 5, 5, 'Aucun relation'),
(3, 1, 1, 'Ministère'),
(3, 2, 2, 'Equipe'),
(3, 3, 3, 'Etude la bible'),
(3, 4, 4, 'Classe Ecole du dimanche'),
(4, 1, 1, 'True / False'),
(4, 2, 2, 'Date'),
(4, 3, 3, 'Text Field (50 char)'),
(4, 4, 4, 'Text Field (100 char)'),
(4, 5, 5, 'Text Field (Long)'),
(4, 6, 6, 'Year'),
(4, 7, 7, 'Season'),
(4, 8, 8, 'Number'),
(4, 9, 9, 'Person from Group'),
(4, 10, 10, 'Money'),
(4, 11, 11, 'Phone Number'),
(4, 12, 12, 'Custom Drop-Down List'),
(5, 1, 1, 'bAll'),
(5, 2, 2, 'bAdmin'),
(5, 3, 3, 'bAddRecords'),
(5, 4, 4, 'bEditRecords'),
(5, 5, 5, 'bDeleteRecords'),
(5, 6, 6, 'bMenuOptions'),
(5, 7, 7, 'bManageGroups'),
(5, 8, 8, 'bFinance'),
(5, 9, 9, 'bNotes'),
(5, 10, 10, 'bCommunication'),
(5, 11, 11, 'bCanvasser'),
(10, 1, 1, 'Teacher'),
(10, 2, 2, 'Student'),
(11, 1, 1, 'Member'),
(12, 1, 1, 'Teacher'),
(12, 2, 2, 'Student'),
(14, 1, 1, 'Student'),
(14, 2, 2, 'Teacher'),
(15, 1, 1, 'Member'),
(16, 1, 1, 'Teacher'),
(16, 2, 2, 'Student'),
(17, 1, 1, 'Membre'),
(18, 1, 1, 'Member'),
(19, 1, 1, 'Teacher'),
(19, 2, 2, 'Student'),
(17, 2, 2, 'Responsable'),
(20, 1, 1, 'Member'),
(21, 1, 1, 'Member');

-- --------------------------------------------------------

--
-- Structure de la table `locks`
--

CREATE TABLE `locks` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timeout` int(10) UNSIGNED DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `token` varbinary(100) DEFAULT NULL,
  `scope` tinyint(4) DEFAULT NULL,
  `depth` tinyint(4) DEFAULT NULL,
  `uri` varbinary(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `menu_links`
--

CREATE TABLE `menu_links` (
  `linkId` mediumint(8) UNSIGNED NOT NULL,
  `linkPersonId` mediumint(9) UNSIGNED DEFAULT NULL,
  `linkName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linkUri` text COLLATE utf8_unicode_ci NOT NULL,
  `linkOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `menu_links`
--

INSERT INTO `menu_links` (`linkId`, `linkPersonId`, `linkName`, `linkUri`, `linkOrder`) VALUES
(1, NULL, 'Apple', 'https://www.apple.com', 0),
(2, 1, 'My private Link : apple', 'https://www.apple.com', 0),
(3, NULL, 'Microsoft', 'https://www.microsoft.com', 1);

-- --------------------------------------------------------

--
-- Structure de la table `multibuy_mb`
--

CREATE TABLE `multibuy_mb` (
  `mb_ID` mediumint(9) UNSIGNED NOT NULL,
  `mb_per_ID` mediumint(9) NOT NULL DEFAULT 0,
  `mb_item_ID` mediumint(9) NOT NULL DEFAULT 0,
  `mb_count` decimal(8,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `note_nte`
--

CREATE TABLE `note_nte` (
  `nte_ID` mediumint(8) UNSIGNED NOT NULL,
  `nte_per_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `nte_fam_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `nte_Private` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `nte_Text` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `nte_DateEntered` datetime NOT NULL,
  `nte_DateLastEdited` datetime DEFAULT NULL,
  `nte_EnteredBy` mediumint(8) NOT NULL DEFAULT 0,
  `nte_EditedBy` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `nte_Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nte_Info` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nte_Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
  `nte_isEditedBy` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `nte_isEditedByDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `note_nte`
--

INSERT INTO `note_nte` (`nte_ID`, `nte_per_ID`, `nte_fam_ID`, `nte_Private`, `nte_Text`, `nte_DateEntered`, `nte_DateLastEdited`, `nte_EnteredBy`, `nte_EditedBy`, `nte_Type`, `nte_Info`, `nte_Title`, `nte_isEditedBy`, `nte_isEditedByDate`) VALUES
(1, 1, NULL, 0, 'system user changed password', '2018-01-13 17:39:20', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(36, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(39, 5, NULL, 0, 'Mis à jour', '2018-01-10 21:56:45', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(40, 5, NULL, 0, 'Mis à jour via Famille', '2018-01-14 12:45:53', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(41, 6, NULL, 0, 'Mis à jour via Famille', '2018-01-14 12:45:55', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(42, 7, NULL, 0, 'Mis à jour via Famille', '2018-01-14 12:45:56', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(43, 8, NULL, 0, 'Mis à jour via Famille', '2018-01-14 12:45:56', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(44, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(45, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(52, 8, NULL, 0, 'Mis à jour', '2018-01-14 12:57:03', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(53, 8, NULL, 0, 'Mis à jour', '2018-01-14 14:51:03', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(54, 6, NULL, 0, 'Mis à jour', '2018-01-14 14:52:45', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(55, 6, NULL, 0, 'Mis à jour', '2018-01-14 14:52:51', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(56, 7, NULL, 0, 'Mis à jour', '2018-01-15 02:20:38', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(59, 15, NULL, 0, 'Créé', '2018-01-15 16:04:58', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(60, 15, NULL, 0, 'Mis à jour', '2018-01-15 16:05:03', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(61, 8, NULL, 0, 'Mis à jour', '2018-01-16 06:35:00', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(62, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-01-21 12:16:06', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(63, 6, NULL, 0, 'Ajouté au groupe: 0 -3 ans', '2018-01-21 15:47:29', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(64, 12, NULL, 0, 'Ajouté au groupe: 0 -3 ans', '2018-01-21 15:47:38', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(65, 9, NULL, 0, 'Ajouté au groupe: 0 -3 ans', '2018-01-21 15:47:57', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(66, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-01-22 10:51:51', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(67, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-01-24 18:19:31', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(68, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(69, NULL, 1, 0, 'Désactiver la famille.', '2018-01-29 14:02:16', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(70, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(71, NULL, 1, 0, 'Activer la famille.', '2018-01-29 14:02:57', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(72, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(73, NULL, 1, 0, 'Désactiver la famille.', '2018-01-29 14:06:24', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(74, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:41:03', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(75, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:41:34', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(76, 6, NULL, 0, 'Ajouté au groupe: 0 -3 ans', '2018-01-29 16:46:22', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(77, 6, NULL, 0, 'Ajouté au groupe: 0 -3 ans', '2018-01-29 16:46:48', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(78, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:54:21', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(79, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:54:35', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(80, NULL, 3, 0, 'Créé', '2018-01-29 16:55:04', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(81, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:55:04', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(82, 6, NULL, 0, 'Mis à jour', '2018-01-29 16:55:56', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(83, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(84, NULL, 1, 0, 'Activer la famille.', '2018-02-01 21:46:51', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(85, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-02-01 21:56:23', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(90, 7, NULL, 0, 'Mis à jour', '2018-02-14 08:15:21', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(91, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-02-14 08:32:43', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(92, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-02-15 15:15:21', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(93, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-02-15 15:15:55', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(94, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2018-03-02 16:17:58', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(97, NULL, 4, 0, 'Créé', '2018-03-12 10:54:13', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(98, 16, NULL, 0, 'Créé', '2018-03-12 10:54:13', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(105, NULL, 5, 0, 'Créé', '2018-04-02 22:59:25', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(106, 17, NULL, 0, 'Créé', '2018-04-02 22:59:25', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(109, 1, NULL, 0, 'admin/AllWindows.csv', '2018-04-03 12:19:48', '2018-04-03 12:20:28', 1, 1, 'file', 'Mise à jour fichier', '', 0, NULL),
(115, 7, NULL, 0, 'Mis à jour', '2018-04-16 07:44:04', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(119, NULL, 6, 0, 'Créé', '2018-05-09 09:28:11', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(137, 1, NULL, 0, 'Mis à jour', '2018-06-16 23:05:26', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(138, 1, NULL, 0, 'Mis à jour', '2018-06-16 23:05:40', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(139, 1, NULL, 0, 'Mis à jour', '2018-06-16 23:05:54', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(145, 18, NULL, 0, 'Créé', '2018-06-17 09:20:52', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(146, 18, NULL, 0, 'Mis à jour', '2018-06-17 09:21:03', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(147, 18, NULL, 0, 'Mis à jour', '2018-06-17 09:21:12', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(148, 18, NULL, 0, 'Mis à jour', '2018-06-17 09:21:23', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(149, 18, NULL, 0, 'Mis à jour', '2018-06-17 09:22:25', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(150, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:41:39', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(151, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:41:39', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(152, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:44:35', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(153, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:44:45', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(154, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:44:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(155, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:44:53', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(156, 5, NULL, 0, 'Mis à jour', '2018-06-17 12:45:13', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(157, 5, NULL, 0, 'Mis à jour', '2018-06-17 17:47:17', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(158, 1, NULL, 0, 'Mis à jour', '2018-06-22 08:35:31', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(159, 1, NULL, 0, 'Mis à jour', '2018-06-22 08:35:40', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(160, 1, NULL, 0, 'Mis à jour', '2018-06-22 08:35:47', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(161, 1, NULL, 0, 'Mis à jour', '2018-06-22 08:35:56', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(162, 5, NULL, 0, 'Ajouté au groupe: Conseil spirituel', '2018-06-29 11:10:38', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(163, 5, NULL, 0, 'Mis à jour', '2018-06-17 17:47:17', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(164, 5, NULL, 0, 'Personne désactivée', '2018-08-16 12:33:48', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(165, 5, NULL, 0, 'Mis à jour', '2018-06-17 17:47:17', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(166, 5, NULL, 0, 'Personne activée', '2018-08-16 12:34:07', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(167, 1, NULL, 0, 'admin/test.txt', '2018-08-16 23:22:48', '2019-02-06 18:00:46', 1, 0, 'file', 'Create file', 'test', 0, NULL),
(168, 6, NULL, 0, 'Updated', '2018-08-21 15:46:57', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(169, 5, NULL, 0, 'Updated', '2018-08-25 20:48:27', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(170, 5, NULL, 0, 'system user created', '2018-08-25 20:48:51', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(171, 5, NULL, 0, 'system user created', '2018-08-25 20:48:51', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(172, 5, NULL, 0, 'Updated', '2018-08-25 20:48:27', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(173, NULL, NULL, 0, 'Person Deactivated', '2018-08-25 20:48:55', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(174, 5, NULL, 0, 'Updated', '2018-08-25 20:48:27', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(175, NULL, NULL, 0, 'Person Activated', '2018-08-25 20:48:58', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(176, 5, NULL, 0, 'system user updated', '2018-08-25 20:50:54', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(177, 5, NULL, 0, 'system user updated', '2018-08-25 20:51:08', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(178, 1, NULL, 0, 'admin/Directory-20180608-183521.pdf', '2018-08-26 12:47:40', NULL, 1, 0, 'file', 'Create file', 'essai', 0, NULL),
(179, 1, NULL, 0, '<p><iframe src=\"https://www.youtube.com/embed/roD8D7K9moE\" scrolling=\"no\" frameborder=\"0\" width=\"400\" height=\"250\"></iframe></p>', '2018-08-26 21:52:14', '2018-08-26 21:52:43', 1, 1, 'video', NULL, 'My first Video', 0, NULL),
(180, 18, NULL, 0, '<p>Test</p>', '2018-08-27 09:44:01', NULL, 1, 0, 'note', NULL, 'Test', 0, NULL),
(181, 6, NULL, 0, 'Updated', '2018-09-05 19:39:42', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(182, 5, NULL, 0, 'Ajouté au groupe: class test', '2018-09-22 20:57:39', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(183, 18, NULL, 0, 'Ajouté au groupe: class test', '2018-09-22 20:59:10', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(184, 18, NULL, 0, 'Supprimé du groupe: class test', '2018-09-23 21:48:17', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(185, 18, NULL, 0, 'Ajouté au groupe: class test', '2018-09-23 21:48:30', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(186, 5, NULL, 0, 'Supprimé du groupe: class test', '2018-09-23 21:48:36', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(187, 5, NULL, 0, 'Ajouté au groupe: class test', '2018-09-23 21:48:43', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(188, 1, NULL, 0, 'admin/loibinomiale.pdf', '2018-10-10 23:06:20', NULL, 1, 0, 'file', 'Crear  Ficheros', 'Maths', 0, NULL),
(189, 1, NULL, 0, 'admin/essai/Capture d’écran 2018-10-10 à 18.44.14.png', '2018-10-11 16:06:59', NULL, 1, 0, 'file', 'Crear  Ficheros', 'a image', 0, NULL),
(190, NULL, 1, 0, 'Mis à jour', '2018-01-14 12:45:52', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(191, 18, NULL, 0, 'Mis à jour', '2018-10-13 06:35:42', NULL, 1, 0, 'edit', NULL, '', 0, NULL),
(192, 5, NULL, 0, 'halpha/Un dossier/Capture d’écran 2018-10-12 à 23.48.28.png', '2018-10-13 09:10:35', NULL, 1, 0, 'file', 'Création fichier', 'eee', 0, NULL),
(213, NULL, 8, 0, 'Créé', '2018-10-14 08:38:49', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(214, NULL, 8, 0, 'Créé', '2018-10-14 08:38:49', NULL, 1, 0, 'create', NULL, '', 0, NULL),
(215, 1, NULL, 0, 'system user updated', '2019-02-01 04:52:29', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(216, 1, NULL, 0, 'system user updated', '2019-02-01 04:53:41', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(217, 1, NULL, 0, 'system user updated', '2019-02-01 05:01:18', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(218, 5, NULL, 0, 'system user deleted', '2019-02-01 05:02:26', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(219, 5, NULL, 0, 'system user created', '2019-02-01 05:06:28', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(220, 5, NULL, 0, 'system user created', '2019-02-01 05:06:28', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(221, 5, NULL, 0, 'system user deleted', '2019-02-01 05:06:49', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(222, 5, NULL, 0, 'system user created', '2019-02-01 05:12:38', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(223, 5, NULL, 0, 'system user created', '2019-02-01 05:12:38', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(224, 5, NULL, 0, 'system user deleted', '2019-02-01 05:12:47', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(225, 5, NULL, 0, 'system user created', '2019-02-01 08:09:06', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(226, 5, NULL, 0, 'system user created', '2019-02-01 08:09:06', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(227, 5, NULL, 0, 'system user deleted', '2019-02-01 08:09:12', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(228, 5, NULL, 0, 'system user created', '2019-02-01 08:09:24', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(229, 5, NULL, 0, 'system user created', '2019-02-01 08:09:24', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(230, 5, NULL, 0, 'system user updated', '2019-02-01 08:09:37', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(231, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2019-02-01 08:10:42', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(232, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2019-02-02 15:55:17', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(233, 1, NULL, 0, 'un utilisateur système a été mis à jour', '2019-02-02 15:55:28', NULL, 1, 0, 'user', NULL, '', 0, NULL),
(234, 5, NULL, 0, 'Supprimé du groupe: Conseil spirituel', '2019-02-06 17:59:51', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(235, 5, NULL, 0, 'Ajouté au groupe: Conseil spirituel', '2019-02-06 18:00:00', NULL, 1, 0, 'group', NULL, '', 0, NULL),
(236, 5, NULL, 1, 'toto/podcast.png', '2019-02-07 01:11:02', NULL, 1, 0, 'file', 'Création fichier', 'podcast.png', 0, NULL),
(237, 5, NULL, 1, 'toto/podcast.png', '2019-02-07 01:11:07', NULL, 1, 0, 'file', 'Création fichier', 'podcast.png', 0, NULL),
(238, 5, NULL, 1, 'toto/podcast.png', '2019-02-07 01:11:41', NULL, 1, 0, 'file', 'Création fichier', 'podcast.png', 0, NULL),
(239, 1, NULL, 1, 'admin/podcast.png', '2019-02-07 01:12:38', NULL, 1, 0, 'file', 'Création fichier', 'podcast.png', 0, NULL),
(240, 1, NULL, 1, 'admin/ski.jpg', '2019-02-07 01:13:47', NULL, 1, 0, 'file', 'Création fichier', 'ski.jpg', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `note_nte_share`
--

CREATE TABLE `note_nte_share` (
  `nte_sh_id` mediumint(9) UNSIGNED NOT NULL,
  `nte_sh_note_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `nte_sh_share_to_person_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `nte_sh_share_to_family_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `nte_sh_share_rights` smallint(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `note_nte_share`
--

INSERT INTO `note_nte_share` (`nte_sh_id`, `nte_sh_note_ID`, `nte_sh_share_to_person_ID`, `nte_sh_share_to_family_ID`, `nte_sh_share_rights`) VALUES
(1, 167, 18, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `paddlenum_pn`
--

CREATE TABLE `paddlenum_pn` (
  `pn_ID` mediumint(9) UNSIGNED NOT NULL,
  `pn_fr_ID` mediumint(9) UNSIGNED DEFAULT NULL,
  `pn_Num` mediumint(9) UNSIGNED DEFAULT NULL,
  `pn_per_ID` mediumint(9) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pastoral_care`
--

CREATE TABLE `pastoral_care` (
  `pst_cr_id` mediumint(9) UNSIGNED NOT NULL,
  `pst_cr_person_id` mediumint(9) UNSIGNED NOT NULL,
  `pst_cr_pastor_id` mediumint(9) UNSIGNED DEFAULT NULL,
  `pst_cr_pastor_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pst_cr_Type_id` mediumint(9) UNSIGNED NOT NULL,
  `pst_cr_date` datetime DEFAULT NULL,
  `pst_cr_visible` tinyint(1) NOT NULL DEFAULT 0,
  `pst_cr_Text` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `pastoral_care`
--

INSERT INTO `pastoral_care` (`pst_cr_id`, `pst_cr_person_id`, `pst_cr_pastor_id`, `pst_cr_pastor_Name`, `pst_cr_Type_id`, `pst_cr_date`, `pst_cr_visible`, `pst_cr_Text`) VALUES
(1, 5, 1, 'Ecclesia Admin', 1, '2018-08-08 19:59:49', 1, '<p>essai</p>'),
(2, 5, 1, 'Ecclesia Admin', 3, '2018-08-08 20:00:03', 1, '<p>coucou</p>'),
(3, 5, 1, 'Ecclesia Admin', 1, '2018-10-11 16:48:29', 0, '<p>Vu en entretien le 10/10/2018</p>');

-- --------------------------------------------------------

--
-- Structure de la table `pastoral_care_type`
--

CREATE TABLE `pastoral_care_type` (
  `pst_cr_tp_id` mediumint(9) UNSIGNED NOT NULL,
  `pst_cr_tp_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pst_cr_tp_desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pst_cr_tp_visible` tinyint(1) NOT NULL DEFAULT 0,
  `pst_cr_tp_comment` text COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'comment for GDPR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `pastoral_care_type`
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Note pastorale classique', '', 1, ''),
(2, 'Pourquoi êtes-vous venu à l\'église', '', 1, ''),
(3, 'Pourquoi continuez-vous à venir ?', '', 1, ''),
(4, 'Avez-vous une requêtes à nous faire ?', '', 1, ''),
(5, 'Comment avez-vous entendu parler de l\'église ?', '', 1, ''),
(6, 'Baptême', 'Formation', 0, ''),
(7, 'Mariage', 'Formation', 0, ''),
(8, 'Relation d\'aide', 'Thérapie et suivi', 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `person2group2role_p2g2r`
--

CREATE TABLE `person2group2role_p2g2r` (
  `p2g2r_per_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `p2g2r_grp_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `p2g2r_rle_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `person2group2role_p2g2r`
--

INSERT INTO `person2group2role_p2g2r` (`p2g2r_per_ID`, `p2g2r_grp_ID`, `p2g2r_rle_ID`) VALUES
(5, 7, 1),
(5, 8, 2),
(6, 2, 1),
(9, 2, 2),
(12, 2, 1),
(18, 8, 2);

-- --------------------------------------------------------

--
-- Structure de la table `person2volunteeropp_p2vo`
--

CREATE TABLE `person2volunteeropp_p2vo` (
  `p2vo_ID` mediumint(9) NOT NULL,
  `p2vo_per_ID` mediumint(9) UNSIGNED NOT NULL,
  `p2vo_vol_ID` mediumint(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `person2volunteeropp_p2vo`
--

INSERT INTO `person2volunteeropp_p2vo` (`p2vo_ID`, `p2vo_per_ID`, `p2vo_vol_ID`) VALUES
(1, 5, 1),
(2, 5, 2),
(5, 18, 1);

-- --------------------------------------------------------

--
-- Structure de la table `person_custom`
--

CREATE TABLE `person_custom` (
  `per_ID` mediumint(9) NOT NULL DEFAULT 0,
  `c1` enum('false','true') COLLATE utf8_unicode_ci DEFAULT NULL,
  `c2` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `person_custom`
--

INSERT INTO `person_custom` (`per_ID`, `c1`, `c2`) VALUES
(1, NULL, NULL),
(5, 'false', 6),
(6, NULL, NULL),
(18, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `person_custom_master`
--

CREATE TABLE `person_custom_master` (
  `custom_Order` smallint(6) NOT NULL DEFAULT 0,
  `custom_Field` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_Name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_Special` mediumint(8) UNSIGNED DEFAULT NULL,
  `custom_Side` enum('left','right') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'left',
  `custom_FieldSec` tinyint(4) NOT NULL,
  `type_ID` tinyint(4) NOT NULL DEFAULT 0,
  `custom_id` mediumint(9) UNSIGNED NOT NULL,
  `custom_comment` text COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'comment for GDPR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `person_custom_master`
--

INSERT INTO `person_custom_master` (`custom_Order`, `custom_Field`, `custom_Name`, `custom_Special`, `custom_Side`, `custom_FieldSec`, `type_ID`, `custom_id`, `custom_comment`) VALUES
(1, 'c1', 'boolTest', NULL, 'left', 1, 1, 1, ''),
(2, 'c2', 'Personne', 2, 'left', 1, 9, 2, '');

-- --------------------------------------------------------

--
-- Structure de la table `person_per`
--

CREATE TABLE `person_per` (
  `per_ID` mediumint(9) UNSIGNED NOT NULL,
  `per_Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_MiddleName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Suffix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Address1` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Address2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Zip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_HomePhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_WorkPhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_CellPhone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_Email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_WorkEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_BirthMonth` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `per_BirthDay` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `per_BirthYear` year(4) DEFAULT NULL,
  `per_MembershipDate` date DEFAULT NULL,
  `per_Gender` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `per_fmr_ID` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `per_cls_ID` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `per_fam_ID` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `per_Envelope` smallint(5) UNSIGNED DEFAULT NULL,
  `per_DateLastEdited` datetime DEFAULT NULL,
  `per_DateEntered` datetime NOT NULL,
  `per_EnteredBy` smallint(5) NOT NULL DEFAULT 0,
  `per_EditedBy` smallint(5) UNSIGNED DEFAULT 0,
  `per_FriendDate` date DEFAULT NULL,
  `per_Flags` mediumint(9) NOT NULL DEFAULT 0,
  `per_FacebookID` bigint(20) UNSIGNED DEFAULT NULL,
  `per_Twitter` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_LinkedIn` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `per_DateDeactivated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `person_per`
--

INSERT INTO `person_per` (`per_ID`, `per_Title`, `per_FirstName`, `per_MiddleName`, `per_LastName`, `per_Suffix`, `per_Address1`, `per_Address2`, `per_City`, `per_State`, `per_Zip`, `per_Country`, `per_HomePhone`, `per_WorkPhone`, `per_CellPhone`, `per_Email`, `per_WorkEmail`, `per_BirthMonth`, `per_BirthDay`, `per_BirthYear`, `per_MembershipDate`, `per_Gender`, `per_fmr_ID`, `per_cls_ID`, `per_fam_ID`, `per_Envelope`, `per_DateLastEdited`, `per_DateEntered`, `per_EnteredBy`, `per_EditedBy`, `per_FriendDate`, `per_Flags`, `per_FacebookID`, `per_Twitter`, `per_LinkedIn`, `per_DateDeactivated`) VALUES
(1, '', 'Ecclesia', '', 'Admin', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0000, '2018-06-13', 0, 0, 1, 0, 0, '2018-06-22 08:35:56', '2018-06-22 08:35:56', 1, 1, NULL, 0, 0, '', '', NULL),
(5, '', 'Henriette', '', 'Alpha', '', '', '', '', '', '', '', '', '', '', 'henriette@toto.fr', '', 2, 2, 1981, '2018-06-07', 2, 2, 1, 1, 0, '2018-08-25 20:48:27', '2018-08-25 20:48:27', 1, 1, '2018-06-14', 0, 0, '', '', NULL),
(6, '', 'Rohan', '', 'Alexander', '', '', '', '', '', '', '', '', '', '', 'rohan.alex@virgin.net', 'rohan.alex@virgin.net', 3, 3, 2016, NULL, 2, 3, 2, 3, 0, '2018-09-05 19:39:42', '2018-09-05 19:39:42', 1, 1, NULL, 0, 0, '', '', NULL),
(7, '', 'Lola', '', 'Alpha', '', '', '', '', '', '', '', '', '', '', '', '', 4, 4, 2002, NULL, 2, 3, 2, 1, 0, '2018-04-16 07:44:04', '2018-04-16 07:44:04', 1, 1, NULL, 0, 0, '', '', NULL),
(8, '', 'Marc', '', 'Alpha', '', '', '', '', '', '', '', '', '', '', '', '', 5, 5, 2005, NULL, 1, 3, 2, 1, 0, '2018-01-16 06:35:00', '2018-01-16 06:35:00', 1, 1, NULL, 0, 0, '', '', NULL),
(9, NULL, 'Pierre', '', 'Beta', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1980, NULL, 1, 1, 1, 2, NULL, NULL, '2018-01-10 22:02:14', 1, 0, NULL, 0, NULL, NULL, NULL, NULL),
(10, '', 'Marie', '', 'Beta', '', '', '', '', '', '', '', '', '', '', '', '', 2, 2, 1982, NULL, 2, 2, 2, 2, 0, '2018-01-12 14:33:18', '2018-01-12 14:33:18', 1, 1, NULL, 0, 0, '', '', NULL),
(11, NULL, 'Pedro', '', 'Beta', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 3, 2016, NULL, 1, 3, 2, 2, NULL, NULL, '2018-01-10 22:02:15', 1, 0, NULL, 0, NULL, NULL, NULL, NULL),
(12, NULL, 'Antonio', '', 'Beta', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 4, 2015, NULL, 1, 3, 2, 2, NULL, NULL, '2018-01-10 22:02:15', 1, 0, NULL, 0, NULL, NULL, NULL, NULL),
(15, 'Mr', 'Toto', '', 'toto', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0000, NULL, 0, 0, 0, 0, 0, '2018-01-15 16:05:03', '2018-01-15 16:05:03', 1, 1, '2018-01-15', 0, 0, '', '', NULL),
(16, '', 'Seul', '', 'Seul', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0000, NULL, 0, 0, 0, 4, 0, NULL, '2018-03-12 10:54:13', 1, 0, '2018-03-12', 0, 0, '', '', NULL),
(17, 'Mr', 'seul', '', 'seul', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0000, NULL, 0, 0, 0, 5, 0, NULL, '2018-04-02 22:59:25', 1, 0, '2018-04-02', 0, 0, '', '', NULL),
(18, 'Mr', 'Henri', '', 'Alpha', '', '', '', '', '', '', '', '', '', '', '', '', 1, 1, 1980, '2018-06-21', 0, 1, 1, 1, 0, '2018-10-13 06:35:42', '2018-10-13 06:35:42', 1, 1, '2018-06-15', 0, 0, '', '', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `pledge_plg`
--

CREATE TABLE `pledge_plg` (
  `plg_plgID` mediumint(9) NOT NULL,
  `plg_FamID` mediumint(9) DEFAULT NULL,
  `plg_FYID` mediumint(9) DEFAULT NULL,
  `plg_date` date DEFAULT NULL,
  `plg_amount` decimal(8,2) DEFAULT NULL,
  `plg_schedule` enum('Weekly','Monthly','Quarterly','Once','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `plg_method` enum('CREDITCARD','CHECK','CASH','BANKDRAFT','EGIVE') COLLATE utf8_unicode_ci DEFAULT NULL,
  `plg_comment` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `plg_DateLastEdited` date NOT NULL DEFAULT '2016-01-01',
  `plg_EditedBy` mediumint(9) NOT NULL DEFAULT 0,
  `plg_PledgeOrPayment` enum('Pledge','Payment') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pledge',
  `plg_fundID` tinyint(3) UNSIGNED DEFAULT NULL,
  `plg_depID` mediumint(9) UNSIGNED DEFAULT NULL,
  `plg_CheckNo` bigint(16) UNSIGNED DEFAULT NULL,
  `plg_Problem` tinyint(1) DEFAULT NULL,
  `plg_scanString` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `plg_aut_ID` mediumint(9) NOT NULL DEFAULT 0,
  `plg_aut_Cleared` tinyint(1) NOT NULL DEFAULT 0,
  `plg_aut_ResultID` mediumint(9) NOT NULL DEFAULT 0,
  `plg_NonDeductible` decimal(8,2) NOT NULL,
  `plg_GroupKey` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `plg_statut` enum('invalidate','validate') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'invalidate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `pledge_plg`
--

INSERT INTO `pledge_plg` (`plg_plgID`, `plg_FamID`, `plg_FYID`, `plg_date`, `plg_amount`, `plg_schedule`, `plg_method`, `plg_comment`, `plg_DateLastEdited`, `plg_EditedBy`, `plg_PledgeOrPayment`, `plg_fundID`, `plg_depID`, `plg_CheckNo`, `plg_Problem`, `plg_scanString`, `plg_aut_ID`, `plg_aut_Cleared`, `plg_aut_ResultID`, `plg_NonDeductible`, `plg_GroupKey`, `plg_statut`) VALUES
(1, 1, 22, '2018-01-31', '120.00', 'Once', 'CASH', '', '2018-02-03', 1, 'Payment', 1, 1, 0, NULL, '', 0, 0, 0, '0.00', 'cash|0|1|1|2018-01-31', 'invalidate'),
(5, 2, 22, '2018-01-17', '10.00', 'Once', 'CHECK', '', '2018-02-01', 1, 'Payment', 1, 5, 12345, NULL, '', 0, 0, 0, '0.00', '10|0|2|1|2018-01-17', 'invalidate'),
(6, 1, 22, '2018-01-31', '1234.00', 'Once', 'BANKDRAFT', '', '2018-02-03', 1, 'Payment', 1, 6, NULL, NULL, '', 0, 0, 0, '0.00', 'draft|0|1|1|2018-01-31', 'invalidate'),
(7, 1, 22, '2018-02-04', '120.00', 'Once', 'CREDITCARD', '', '2018-02-04', 1, 'Payment', 1, 4, NULL, NULL, '', 1, 0, 0, '0.00', '1|0|1|1|2018-02-04', 'invalidate'),
(8, 1, 22, '2018-02-05', '1000.00', 'Once', 'CHECK', '', '2018-09-05', 1, 'Payment', 1, 7, 10000, NULL, '', 0, 0, 0, '0.00', '10000|0|1|1|2018-02-05', 'invalidate'),
(9, 1, 22, '2018-02-12', '456.00', 'Once', 'CHECK', '', '2018-02-12', 1, 'Pledge', 1, 0, 0, NULL, '', 0, 0, 0, '0.00', '0|0|1|1|2018-02-12', 'invalidate'),
(10, 1, 22, '2018-02-12', '123.00', 'Once', 'CASH', '', '2018-03-02', 1, 'Payment', 1, 7, 0, NULL, '', 0, 0, 0, '0.00', '116751751765167|0|1|1|2018-02-12', 'invalidate'),
(11, 1, 22, '2018-02-05', '12.00', 'Once', 'CHECK', '', '2018-09-05', 1, 'Payment', 2, 7, 10000, NULL, '', 0, 0, 0, '0.00', '10000|0|1|1|2018-02-05', 'invalidate'),
(12, 1, 22, '2018-02-12', '56.00', 'Once', 'CASH', '', '2018-03-02', 1, 'Payment', 2, 7, NULL, NULL, '', 0, 0, 0, '0.00', '116751751765167|0|1|1|2018-02-12', 'invalidate'),
(13, 4, 22, '2018-04-03', '12.00', 'Once', 'CASH', '', '2018-04-03', 1, 'Pledge', 2, 7, 0, NULL, '', 0, 0, 0, '0.00', 'cash|0|4|2|2018-04-03', 'invalidate'),
(14, 4, 22, '2018-04-03', '153.00', 'Once', 'CASH', '', '2018-04-03', 1, 'Pledge', 1, 7, NULL, NULL, '', 0, 0, 0, '0.00', 'cash|0|4|2|2018-04-03', 'invalidate'),
(15, 1, 23, '2019-02-03', '566.00', 'Once', 'CHECK', '', '2019-02-03', 1, 'Payment', 1, 9, 189898989, NULL, '', 0, 0, 0, '0.00', '189898989|0|1|1|2019-02-03', 'invalidate'),
(16, 1, 23, '2019-02-03', '123.00', 'Once', 'CHECK', '', '2019-02-03', 1, 'Payment', 2, 9, 189898989, NULL, '', 0, 0, 0, '0.00', '189898989|0|1|1|2019-02-03', 'invalidate'),
(17, 1, 23, '2019-02-03', '566.00', 'Once', 'CREDITCARD', '', '2019-02-03', 1, 'Payment', 1, 10, NULL, NULL, '', 0, 0, 0, '0.00', 'credit|0|1|1|2019-02-03', 'invalidate'),
(18, 1, 23, '2019-02-03', '145.00', 'Once', 'CREDITCARD', '', '2019-02-03', 1, 'Payment', 2, 10, NULL, NULL, '', 0, 0, 0, '0.00', 'credit|0|1|1|2019-02-03', 'invalidate'),
(19, 1, 23, '2019-02-03', '76.00', 'Once', 'CHECK', '', '2019-02-03', 1, 'Payment', 1, 11, 98989898989898, NULL, '', 0, 0, 0, '0.00', '98989898989898|0|1|1|2019-02-03', 'invalidate'),
(20, 1, 23, '2019-02-03', '56.00', 'Once', 'CHECK', '', '2019-02-03', 1, 'Payment', 2, 11, 98989898989898, NULL, '', 0, 0, 0, '0.00', '98989898989898|0|1|1|2019-02-03', 'invalidate');

-- --------------------------------------------------------

--
-- Structure de la table `principals`
--

CREATE TABLE `principals` (
  `id` int(10) UNSIGNED NOT NULL,
  `uri` varbinary(200) NOT NULL,
  `email` varbinary(80) DEFAULT NULL,
  `displayname` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `principals`
--

INSERT INTO `principals` (`id`, `uri`, `email`, `displayname`) VALUES
(1, 0x7072696e636970616c732f61646d696e, 0x61646d696e406578616d706c652e6f7267, 'Administrator'),
(2, 0x7072696e636970616c732f61646d696e2f63616c656e6461722d70726f78792d72656164, NULL, NULL),
(3, 0x7072696e636970616c732f61646d696e2f63616c656e6461722d70726f78792d7772697465, NULL, NULL),
(8, 0x7072696e636970616c732f746f746f, 0x68656e72696574746540746f746f2e6672, 'toto');

-- --------------------------------------------------------

--
-- Structure de la table `propertystorage`
--

CREATE TABLE `propertystorage` (
  `id` int(10) UNSIGNED NOT NULL,
  `path` varbinary(1024) NOT NULL,
  `name` varbinary(100) NOT NULL,
  `valuetype` int(10) UNSIGNED DEFAULT NULL,
  `value` mediumblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `propertytype_prt`
--

CREATE TABLE `propertytype_prt` (
  `prt_ID` mediumint(9) NOT NULL,
  `prt_Class` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prt_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prt_Description` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `propertytype_prt`
--

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
(1, 'p', 'Person', 'General Person Properties'),
(2, 'f', 'Family', 'General Family Properties'),
(3, 'g', 'Group', 'General Group Properties'),
(4, 'm', 'Menu', 'To customise the sunday school menu.');

-- --------------------------------------------------------

--
-- Structure de la table `property_pro`
--

CREATE TABLE `property_pro` (
  `pro_ID` mediumint(8) UNSIGNED NOT NULL,
  `pro_Class` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pro_prt_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `pro_Name` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `pro_Description` text COLLATE utf8_unicode_ci NOT NULL,
  `pro_Prompt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pro_Comment` text COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'comment for GDPR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `property_pro`
--

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
(1, 'p', 1, 'Disabled', 'has a disability.', 'What is the nature of the disability?', ''),
(2, 'f', 2, 'Single Parent', 'is a single-parent household.', '', ''),
(3, 'g', 3, 'Youth', 'is youth-oriented.', '', ''),
(4, 'm', 4, 'Mon sous menu', '', '', ''),
(5, 'm', 4, 'Mon sous menu 2', '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `queryparameteroptions_qpo`
--

CREATE TABLE `queryparameteroptions_qpo` (
  `qpo_ID` smallint(5) UNSIGNED NOT NULL,
  `qpo_qrp_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `qpo_Display` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qpo_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `queryparameteroptions_qpo`
--

INSERT INTO `queryparameteroptions_qpo` (`qpo_ID`, `qpo_qrp_ID`, `qpo_Display`, `qpo_Value`) VALUES
(1, 4, 'Male', '1'),
(2, 4, 'Female', '2'),
(3, 6, 'Male', '1'),
(4, 6, 'Female', '2'),
(5, 15, 'Name', 'CONCAT(COALESCE(`per_FirstName`,\'\'),COALESCE(`per_MiddleName`,\'\'),COALESCE(`per_LastName`,\'\'))'),
(6, 15, 'Zip Code', 'fam_Zip'),
(7, 15, 'State', 'fam_State'),
(8, 15, 'City', 'fam_City'),
(9, 15, 'Home Phone', 'per_HomePhone'),
(10, 27, '2012/2013', '17'),
(11, 27, '2013/2014', '18'),
(12, 27, '2014/2015', '19'),
(13, 27, '2015/2016', '20'),
(14, 28, '2012/2013', '17'),
(15, 28, '2013/2014', '18'),
(16, 28, '2014/2015', '19'),
(17, 28, '2015/2016', '20'),
(18, 30, '2012/2013', '17'),
(19, 30, '2013/2014', '18'),
(20, 30, '2014/2015', '19'),
(21, 30, '2015/2016', '20'),
(22, 31, '2012/2013', '17'),
(23, 31, '2013/2014', '18'),
(24, 31, '2014/2015', '19'),
(25, 31, '2015/2016', '20'),
(26, 15, 'Email', 'per_Email'),
(27, 15, 'WorkEmail', 'per_WorkEmail'),
(28, 32, '2012/2013', '17'),
(29, 32, '2013/2014', '18'),
(30, 32, '2014/2015', '19'),
(31, 32, '2015/2016', '20'),
(32, 33, 'Member', '1'),
(33, 33, 'Regular Attender', '2'),
(34, 33, 'Guest', '3'),
(35, 33, 'Non-Attender', '4'),
(36, 33, 'Non-Attender (staff)', '5');

-- --------------------------------------------------------

--
-- Structure de la table `queryparameters_qrp`
--

CREATE TABLE `queryparameters_qrp` (
  `qrp_ID` mediumint(8) UNSIGNED NOT NULL,
  `qrp_qry_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `qrp_Type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `qrp_OptionSQL` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `qrp_Name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qrp_Description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `qrp_Alias` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qrp_Default` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qrp_Required` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `qrp_InputBoxSize` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `qrp_Validation` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qrp_NumericMax` int(11) DEFAULT NULL,
  `qrp_NumericMin` int(11) DEFAULT NULL,
  `qrp_AlphaMinLength` int(11) DEFAULT NULL,
  `qrp_AlphaMaxLength` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `queryparameters_qrp`
--

INSERT INTO `queryparameters_qrp` (`qrp_ID`, `qrp_qry_ID`, `qrp_Type`, `qrp_OptionSQL`, `qrp_Name`, `qrp_Description`, `qrp_Alias`, `qrp_Default`, `qrp_Required`, `qrp_InputBoxSize`, `qrp_Validation`, `qrp_NumericMax`, `qrp_NumericMin`, `qrp_AlphaMinLength`, `qrp_AlphaMaxLength`) VALUES
(1, 4, 0, NULL, 'Minimum Age', 'The minimum age for which you want records returned.', 'min', '0', 0, 5, 'n', 120, 0, NULL, NULL),
(2, 4, 0, NULL, 'Maximum Age', 'The maximum age for which you want records returned.', 'max', '120', 1, 5, 'n', 120, 0, NULL, NULL),
(4, 6, 1, '', 'Gender', 'The desired gender to search the database for.', 'gender', '1', 1, 0, '', 0, 0, 0, 0),
(5, 7, 2, 'SELECT lst_OptionID as Value, lst_OptionName as Display FROM list_lst WHERE lst_ID=2 ORDER BY lst_OptionSequence', 'Family Role', 'Select the desired family role.', 'role', '1', 0, 0, '', 0, 0, 0, 0),
(6, 7, 1, '', 'Gender', 'The gender for which you would like records returned.', 'gender', '1', 1, 0, '', 0, 0, 0, 0),
(8, 9, 2, 'SELECT pro_ID AS Value, pro_Name as Display \r\nFROM property_pro\r\nWHERE pro_Class= \'p\' \r\nORDER BY pro_Name ', 'Property', 'The property for which you would like person records returned.', 'PropertyID', '0', 1, 0, '', 0, 0, 0, 0),
(9, 10, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don ORDER BY don_date ASC', 'Beginning Date', 'Please select the beginning date to calculate total contributions for each member (i.e. YYYY-MM-DD). NOTE: You can only choose dates that conatain donations.', 'startdate', '1', 1, 0, '0', 0, 0, 0, 0),
(10, 10, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don\r\nORDER BY don_date DESC', 'Ending Date', 'Please enter the last date to calculate total contributions for each member (i.e. YYYY-MM-DD).', 'enddate', '1', 1, 0, '', 0, 0, 0, 0),
(14, 15, 0, '', 'Search', 'Enter any part of the following: Name, City, State, Zip, Home Phone, Email, or Work Email.', 'searchstring', '', 1, 0, '', 0, 0, 0, 0),
(15, 15, 1, '', 'Field', 'Select field to search for.', 'searchwhat', '1', 1, 0, '', 0, 0, 0, 0),
(16, 11, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don ORDER BY don_date ASC', 'Beginning Date', 'Please select the beginning date to calculate total contributions for each member (i.e. YYYY-MM-DD). NOTE: You can only choose dates that conatain donations.', 'startdate', '1', 1, 0, '0', 0, 0, 0, 0),
(17, 11, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don\r\nORDER BY don_date DESC', 'Ending Date', 'Please enter the last date to calculate total contributions for each member (i.e. YYYY-MM-DD).', 'enddate', '1', 1, 0, '', 0, 0, 0, 0),
(18, 18, 0, '', 'Month', 'The birthday month for which you would like records returned.', 'birthmonth', '1', 1, 0, '', 12, 1, 1, 2),
(19, 19, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Class', 'The sunday school class for which you would like records returned.', 'group', '1', 1, 0, '', 12, 1, 1, 2),
(20, 20, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Class', 'The sunday school class for which you would like records returned.', 'group', '1', 1, 0, '', 12, 1, 1, 2),
(21, 21, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Registered students', 'Group of registered students', 'group', '1', 1, 0, '', 12, 1, 1, 2),
(22, 22, 0, '', 'Month', 'The membership anniversary month for which you would like records returned.', 'membermonth', '1', 1, 0, '', 12, 1, 1, 2),
(25, 25, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'Choose a volunteer opportunity', 'volopp', '1', 1, 0, '', 12, 1, 1, 2),
(26, 26, 0, '', 'Months', 'Number of months since becoming a friend', 'friendmonths', '1', 1, 0, '', 24, 1, 1, 2),
(27, 28, 1, '', 'First Fiscal Year', 'First fiscal year for comparison', 'fyid1', '9', 1, 0, '', 12, 9, 0, 0),
(28, 28, 1, '', 'Second Fiscal Year', 'Second fiscal year for comparison', 'fyid2', '9', 1, 0, '', 12, 9, 0, 0),
(30, 30, 1, '', 'First Fiscal Year', 'Pledged this year', 'fyid1', '9', 1, 0, '', 12, 9, 0, 0),
(31, 30, 1, '', 'Second Fiscal Year', 'but not this year', 'fyid2', '9', 1, 0, '', 12, 9, 0, 0),
(32, 32, 1, '', 'Fiscal Year', 'Fiscal Year.', 'fyid', '9', 1, 0, '', 12, 9, 0, 0),
(33, 18, 1, '', 'Classification', 'Member, Regular Attender, etc.', 'percls', '1', 1, 0, '', 12, 1, 1, 2),
(34, 33, 0, NULL, 'Year', 'Get all persons who were born before the Year you mentioned.', 'the_year', '2100', 0, 5, 'n', 2100, 0, NULL, NULL),
(100, 100, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'First volunteer opportunity choice', 'volopp1', '1', 1, 0, '', 12, 1, 1, 2),
(101, 100, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'Second volunteer opportunity choice', 'volopp2', '1', 1, 0, '', 12, 1, 1, 2),
(200, 200, 2, 'SELECT custom_field as Value, custom_Name as Display FROM person_custom_master', 'Custom field', 'Choose customer person field', 'custom', '1', 0, 0, '', 0, 0, 0, 0),
(201, 200, 0, '', 'Field value', 'Match custom field to this value', 'value', '1', 0, 0, '', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `query_qry`
--

CREATE TABLE `query_qry` (
  `qry_ID` mediumint(8) UNSIGNED NOT NULL,
  `qry_SQL` text COLLATE utf8_unicode_ci NOT NULL,
  `qry_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `qry_Description` text COLLATE utf8_unicode_ci NOT NULL,
  `qry_Count` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `qry_Type_ID` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `query_qry`
--

INSERT INTO `query_qry` (`qry_ID`, `qry_SQL`, `qry_Name`, `qry_Description`, `qry_Count`, `qry_Type_ID`) VALUES
(1, 'SELECT CONCAT(\'<a href=v2/people/family/view/\',fam_ID,\'>\',fam_Name,\'</a>\') AS \'Family Name\'   FROM family_fam Where fam_WorkPhone != \"\"', 'Family Member Count', 'Returns each family and the total number of people assigned to them.', 0, 2),
(3, 'SELECT CONCAT(\'<a href=v2/people/family/view/\',fam_ID,\'>\',fam_Name,\'</a>\') AS \'Family Name\', COUNT(*) AS \'No.\'\nFROM person_per\nINNER JOIN family_fam\nON fam_ID = per_fam_ID\nGROUP BY per_fam_ID\nORDER BY \'No.\' DESC', 'Family Member Count', 'Returns each family and the total number of people assigned to them.', 0, 2),
(4, 'SELECT per_ID as AddToCart,CONCAT(\'<a href=v2/people/person/view/\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, CONCAT(per_BirthMonth,\'/\',per_BirthDay,\'/\',per_BirthYear) AS \'Birth Date\', DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay))),\'%Y\')+0 AS \'Age\', per_DateDeactivated as \"GDPR\" FROM person_per WHERE DATE_ADD(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay),INTERVAL ~min~ YEAR) <= CURDATE() AND DATE_ADD(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay),INTERVAL (~max~ + 1) YEAR) >= CURDATE() ORDER by Age , Name DESC', 'Person by Age', 'Returns any person records with ages between two given ages.', 1, 1),
(6, 'SELECT COUNT(per_ID) AS Total FROM person_per WHERE per_Gender = ~gender~', 'Total By Gender', 'Total of records matching a given gender.', 0, 8),
(7, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_fmr_ID = ~role~ AND per_Gender = ~gender~', 'Person by Role and Gender', 'Selects person records with the family role and gender specified.', 1, 1),
(9, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, CONCAT(r2p_Value,\' \') AS Value, per_DateDeactivated as \'GDPR\' FROM person_per,record2property_r2p WHERE per_ID = r2p_record_ID AND r2p_pro_ID = ~PropertyID~ ORDER BY per_LastName', 'Person by Property', 'Returns person records which are assigned the given property.', 1, 1),
(15, 'SELECT per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',per_ID,\'>\',COALESCE(`per_FirstName`,\'\'),\' \',COALESCE(`per_MiddleName`,\'\'),\' \',COALESCE(`per_LastName`,\'\'),\'</a>\') AS Name, fam_City as City, fam_State as State, fam_Zip as ZIP, per_HomePhone as HomePhone, per_Email as Email, per_WorkEmail as WorkEmail,per_DateDeactivated as \'GDPR\' FROM person_per RIGHT JOIN family_fam ON family_fam.fam_id = person_per.per_fam_id WHERE ~searchwhat~ LIKE \'%~searchstring~%\'', 'Advanced Search', 'Search by any part of Name, City, State, Zip, Home Phone, Email, or Work Email.', 1, 7),
(18, 'SELECT per_ID as AddToCart, per_BirthDay as Day, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\'  FROM person_per WHERE per_cls_ID=~percls~ AND per_BirthMonth=~birthmonth~ ORDER BY per_BirthDay', 'Birthdays', 'People with birthdays in a particular month', 0, 3),
(21, 'SELECT per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name,per_DateDeactivated as \'GDPR\' FROM person_per LEFT JOIN person2group2role_p2g2r ON per_id = p2g2r_per_ID WHERE p2g2r_grp_ID=~group~ ORDER BY per_LastName', 'Registered students', 'Find Registered students', 1, 5),
(22, 'SELECT per_ID as AddToCart, DAYOFMONTH(per_MembershipDate) as Day, per_MembershipDate AS DATE, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_cls_ID=1 AND MONTH(per_MembershipDate)=~membermonth~ ORDER BY per_MembershipDate', 'Membership anniversaries', 'Members who joined in a particular month', 0, 3),
(23, 'SELECT usr_per_ID as AddToCart, CONCAT(a.per_FirstName,\' \',a.per_LastName) AS Name, a.per_DateDeactivated as \'GDPR\' FROM user_usr LEFT JOIN person_per a ON per_ID=usr_per_ID ORDER BY per_LastName', 'Select database users', 'People who are registered as database users', 0, 5),
(24, 'SELECT per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_cls_id =1', 'Select all members', 'People who are members', 0, 1),
(25, 'SELECT per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, per_DateDeactivated as \'GDPR\' FROM FROM person_per LEFT JOIN person2volunteeropp_p2vo ON per_id = p2vo_per_ID WHERE p2vo_vol_ID = ~volopp~ ORDER BY per_LastName', 'Volunteers', 'Find volunteers for a particular opportunity', 1, 6),
(26, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE DATE_SUB(NOW(),INTERVAL ~friendmonths~ MONTH)<per_FriendDate ORDER BY per_MembershipDate', 'Recent friends', 'Friends who signed up in previous months', 0, 1),
(27, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\'  FROM person_per inner join family_fam on per_fam_ID=fam_ID where per_fmr_ID<>3 AND fam_OkToCanvass=\"TRUE\" ORDER BY fam_Zip', 'Families to Canvass', 'People in families that are ok to canvass.', 0, 2),
(28, 'SELECT fam_Name, a.plg_amount as PlgFY1, b.plg_amount as PlgFY2 from family_fam left join pledge_plg a on a.plg_famID = fam_ID and a.plg_FYID=~fyid1~ and a.plg_PledgeOrPayment=\'Pledge\' left join pledge_plg b on b.plg_famID = fam_ID and b.plg_FYID=~fyid2~ and b.plg_PledgeOrPayment=\'Pledge\' order by fam_Name', 'Pledge comparison', 'Compare pledges between two fiscal years', 1, 4),
(30, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, fam_address1, fam_city, fam_state, fam_zip, per_DateDeactivated as \'GDPR\' FROM person_per join family_fam on per_fam_id=fam_id where per_fmr_id<>3 and per_fam_id in (select fam_id from family_fam inner join pledge_plg a on a.plg_famID=fam_ID and a.plg_FYID=~fyid1~ and a.plg_amount>0) and per_fam_id not in (select fam_id from family_fam inner join pledge_plg b on b.plg_famID=fam_ID and b.plg_FYID=~fyid2~ and b.plg_amount>0)', 'Missing pledges', 'Find people who pledged one year but not another', 1, 4),
(31, 'select per_ID as AddToCart, per_FirstName, per_LastName, per_email, per_DateDeactivated as \'GDPR\'  FROM person_per, autopayment_aut where aut_famID=per_fam_ID and aut_CreditCard!=\"\" and per_email!=\"\" and (per_fmr_ID=1 or per_fmr_ID=2 or per_cls_ID=1)', 'Credit Card People', 'People who are configured to pay by credit card.', 0, 4),
(32, 'SELECT fam_Name, fam_Envelope, b.fun_Name as Fund_Name, a.plg_amount as Pledge from family_fam left join pledge_plg a on a.plg_famID = fam_ID and a.plg_FYID=~fyid~ and a.plg_PledgeOrPayment=\'Pledge\' and a.plg_amount>0 join donationfund_fun b on b.fun_ID = a.plg_fundID order by fam_Name, a.plg_fundID', 'Family Pledge by Fiscal Year', 'Pledge summary by family name for each fund for the selected fiscal year', 1, 4),
(33, 'SELECT per_ID as AddToCart, per_LastName, per_FirstName, per_DateDeactivated as \'GDPR\'  FROM `person_per` where per_BirthYear<~the_year~ AND per_cls_ID IN (1,2) AND per_fam_ID<>3 AND `per_ID` NOT IN (SELECT p2g2r_per_ID FROM `person2group2role_p2g2r`) order by per_LastName ASC, per_FirstName ASC', 'Person not assigned to a group', 'Returns all the persons not assigned to a group.', 1, 1),
(34, 'SELECT per_ID as AddToCart,per_FirstName, per_LastName, grp_Name, per_DateDeactivated as \'GDPR\'  FROM `person2group2role_p2g2r`, `person_per`, group_grp WHERE per_cls_ID IN (1,2) AND per_fam_ID<>3 AND p2g2r_per_ID=per_ID and grp_ID=p2g2r_grp_ID order by per_FirstName ASC, per_LastName ASC, grp_Name ASC', 'Person assigned to a group', 'Returns all persons assigned to a group.', 1, 1),
(100, 'SELECT a.per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\'  FROM person_per AS a LEFT JOIN person2volunteeropp_p2vo p2v1 ON (a.per_id = p2v1.p2vo_per_ID AND p2v1.p2vo_vol_ID = ~volopp1~) LEFT JOIN person2volunteeropp_p2vo p2v2 ON (a.per_id = p2v2.p2vo_per_ID AND p2v2.p2vo_vol_ID = ~volopp2~) WHERE p2v1.p2vo_per_ID=p2v2.p2vo_per_ID ORDER BY per_LastName', 'Volunteers', 'Find volunteers for who match two specific opportunity codes', 1, 6),
(200, 'SELECT a.per_ID as AddToCart, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\'  FROM person_per AS a LEFT JOIN person_custom pc ON a.per_id = pc.per_ID WHERE pc.~custom~ LIKE \'%~value~%\' ORDER BY per_LastName', 'CustomSearch', 'Find people with a custom field value', 1, 7);

-- --------------------------------------------------------

--
-- Structure de la table `query_type`
--

CREATE TABLE `query_type` (
  `qry_type_id` int(11) NOT NULL,
  `qry_type_Category` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `query_type`
--

INSERT INTO `query_type` (`qry_type_id`, `qry_type_Category`) VALUES
(1, 'Person'),
(2, 'Family'),
(3, 'Events'),
(4, 'Pledges and Payments'),
(5, 'Users'),
(6, 'Volunteers'),
(7, 'Advanced Search'),
(8, 'Not assigned');

-- --------------------------------------------------------

--
-- Structure de la table `record2property_r2p`
--

CREATE TABLE `record2property_r2p` (
  `r2p_pro_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `r2p_record_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `r2p_Value` text COLLATE utf8_unicode_ci NOT NULL,
  `r2p_id` mediumint(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `record2property_r2p`
--

INSERT INTO `record2property_r2p` (`r2p_pro_ID`, `r2p_record_ID`, `r2p_Value`, `r2p_id`) VALUES
(1, 12, 'echo', 1),
(4, 1, 'Menu', 3),
(5, 2, 'Menu', 4),
(4, 8, 'Menu', 5),
(1, 5, 'eee', 6),
(1, 18, 'eeee', 7);

-- --------------------------------------------------------

--
-- Structure de la table `result_res`
--

CREATE TABLE `result_res` (
  `res_ID` mediumint(9) NOT NULL,
  `res_echotype1` text COLLATE utf8_unicode_ci NOT NULL,
  `res_echotype2` text COLLATE utf8_unicode_ci NOT NULL,
  `res_echotype3` text COLLATE utf8_unicode_ci NOT NULL,
  `res_authorization` text COLLATE utf8_unicode_ci NOT NULL,
  `res_order_number` text COLLATE utf8_unicode_ci NOT NULL,
  `res_reference` text COLLATE utf8_unicode_ci NOT NULL,
  `res_status` text COLLATE utf8_unicode_ci NOT NULL,
  `res_avs_result` text COLLATE utf8_unicode_ci NOT NULL,
  `res_security_result` text COLLATE utf8_unicode_ci NOT NULL,
  `res_mac` text COLLATE utf8_unicode_ci NOT NULL,
  `res_decline_code` text COLLATE utf8_unicode_ci NOT NULL,
  `res_tran_date` text COLLATE utf8_unicode_ci NOT NULL,
  `res_merchant_name` text COLLATE utf8_unicode_ci NOT NULL,
  `res_version` text COLLATE utf8_unicode_ci NOT NULL,
  `res_EchoServer` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `schedulingobjects`
--

CREATE TABLE `schedulingobjects` (
  `id` int(11) UNSIGNED NOT NULL,
  `principaluri` varbinary(255) DEFAULT NULL,
  `calendardata` mediumblob DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `lastmodified` int(11) UNSIGNED DEFAULT NULL,
  `etag` varbinary(32) DEFAULT NULL,
  `size` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tokens`
--

CREATE TABLE `tokens` (
  `token` varchar(99) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `reference_id` int(9) NOT NULL,
  `valid_until_date` datetime DEFAULT NULL,
  `remainingUses` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `tokens`
--

INSERT INTO `tokens` (`token`, `type`, `reference_id`, `valid_until_date`, `remainingUses`) VALUES
('88d4286c-d183-4ecb-b802-9ad3fe532d2f', 'secret', -1, '2019-02-15 06:31:39', 5);

-- --------------------------------------------------------

--
-- Structure de la table `userconfig_ucfg`
--

CREATE TABLE `userconfig_ucfg` (
  `ucfg_per_id` mediumint(9) UNSIGNED NOT NULL,
  `ucfg_id` int(11) NOT NULL DEFAULT 0,
  `ucfg_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ucfg_value` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ucfg_type` enum('text','number','date','boolean','textarea') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `ucfg_tooltip` text COLLATE utf8_unicode_ci NOT NULL,
  `ucfg_permission` enum('FALSE','TRUE') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FALSE',
  `ucfg_cat` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `userconfig_ucfg`
--

INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
(0, 0, 'bEmailMailto', '1', 'boolean', 'User permission to send email via mailto: links', 'TRUE', ''),
(0, 1, 'sMailtoDelimiter', ',', 'text', 'Delimiter to separate emails in mailto: links', 'TRUE', ''),
(0, 5, 'bCreateDirectory', '0', 'boolean', 'User permission to create directories', 'FALSE', 'SECURITY'),
(0, 6, 'bExportCSV', '0', 'boolean', 'User permission to export CSV files', 'FALSE', 'SECURITY'),
(0, 8, 'bShowTooltip', '1', 'boolean', 'Allow to see ballon Help', 'TRUE', ''),
(0, 10, 'bAddEvent', '0', 'boolean', 'Allow user to add new event', 'FALSE', 'SECURITY'),
(0, 12, 'bSidebarExpandOnHover', '1', 'boolean', 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
(0, 13, 'bSidebarCollapse', '1', 'boolean', 'The sidebar is collapse by default', 'TRUE', ''),
(1, 0, 'bEmailMailto', '1', 'boolean', 'User permission to send email via mailto: links', 'TRUE', ''),
(1, 1, 'sMailtoDelimiter', ',', 'text', 'user permission to send email via mailto: links', 'TRUE', ''),
(1, 5, 'bCreateDirectory', '1', 'boolean', 'User permission to create directories', 'TRUE', ''),
(1, 6, 'bExportCSV', '1', 'boolean', 'User permission to export CSV files', 'TRUE', ''),
(1, 8, 'bShowTooltip', '1', 'boolean', 'Allow to see ballon Help', 'TRUE', ''),
(1, 10, 'bAddEvent', '1', 'boolean', 'Allow user to add new event', 'TRUE', 'SECURITY'),
(1, 12, 'bSidebarExpandOnHover', '1', 'boolean', 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
(1, 13, 'bSidebarCollapse', '', 'boolean', 'The sidebar is collapse by default', 'TRUE', ''),
(4, 0, 'bEmailMailto', '1', 'boolean', 'User permission to send email via mailto: links', 'TRUE', ''),
(4, 1, 'sMailtoDelimiter', ',', 'text', 'Delimiter to separate emails in mailto: links', 'TRUE', ''),
(4, 5, 'bCreateDirectory', '', 'boolean', 'User permission to create directories', 'FALSE', 'SECURITY'),
(4, 6, 'bExportCSV', '', 'boolean', 'User permission to export CSV files', 'FALSE', 'SECURITY'),
(4, 8, 'bShowTooltip', '1', 'boolean', 'Allow to see ballon Help', 'TRUE', ''),
(4, 10, 'bAddEvent', '', 'boolean', 'Allow user to add new event', 'FALSE', 'SECURITY'),
(5, 0, 'bEmailMailto', '', 'boolean', 'User permission to send email via mailto: links', 'FALSE', ''),
(5, 1, 'sMailtoDelimiter', ',', 'text', 'Delimiter to separate emails in mailto: links', 'TRUE', ''),
(5, 5, 'bCreateDirectory', '', 'boolean', 'User permission to create directories', 'FALSE', 'SECURITY'),
(5, 6, 'bExportCSV', '', 'boolean', 'User permission to export CSV files', 'FALSE', 'SECURITY'),
(5, 8, 'bShowTooltip', '1', 'boolean', 'Allow to see ballon Help', 'TRUE', ''),
(5, 10, 'bAddEvent', '', 'boolean', 'Allow user to add new event', 'FALSE', 'SECURITY'),
(5, 12, 'bSidebarExpandOnHover', '1', 'boolean', 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
(5, 13, 'bSidebarCollapse', '1', 'boolean', 'The sidebar is collapse by default', 'TRUE', '');

-- --------------------------------------------------------

--
-- Structure de la table `userrole_usrrol`
--

CREATE TABLE `userrole_usrrol` (
  `usrrol_id` mediumint(11) UNSIGNED NOT NULL,
  `usrrol_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `usrrol_global` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `usrrol_permissions` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `usrrol_value` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `userrole_usrrol`
--

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:TRUE;bExportSundaySchoolPDF:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1'),
(2, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0;Style:skin-yellow-light', 'bEmailMailto:FALSE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:FALSE;bExportSundaySchoolPDF:FALSE;bCreateDirectory:FALSE;bExportCSV:FALSE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:;sMailtoDelimiter:,;bExportSundaySchoolCSV:;bExportSundaySchoolPDF:;bCreateDirectory:;bExportCSV:;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1'),
(3, 'User Max but not Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:TRUE;bExportSundaySchoolPDF:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1');

-- --------------------------------------------------------

--
-- Structure de la table `user_usr`
--

CREATE TABLE `user_usr` (
  `usr_per_ID` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `usr_Password` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `usr_NeedPasswordChange` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `usr_LastLogin` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `usr_LoginCount` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `usr_FailedLogins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `usr_AddRecords` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_EditRecords` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_DeleteRecords` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_MenuOptions` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_ManageGroups` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_Finance` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_Notes` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_Admin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_SearchLimit` tinyint(4) DEFAULT 10,
  `usr_Style` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'Style.css',
  `usr_showPledges` tinyint(1) NOT NULL DEFAULT 0,
  `usr_showPayments` tinyint(1) NOT NULL DEFAULT 0,
  `usr_showSince` date NOT NULL DEFAULT '2016-01-01',
  `usr_defaultFY` mediumint(9) NOT NULL DEFAULT 10,
  `usr_currentDeposit` mediumint(9) NOT NULL DEFAULT 0,
  `usr_UserName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_EditSelf` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_CalStart` date DEFAULT NULL,
  `usr_CalEnd` date DEFAULT NULL,
  `usr_CalNoSchool1` date DEFAULT NULL,
  `usr_CalNoSchool2` date DEFAULT NULL,
  `usr_CalNoSchool3` date DEFAULT NULL,
  `usr_CalNoSchool4` date DEFAULT NULL,
  `usr_CalNoSchool5` date DEFAULT NULL,
  `usr_CalNoSchool6` date DEFAULT NULL,
  `usr_CalNoSchool7` date DEFAULT NULL,
  `usr_CalNoSchool8` date DEFAULT NULL,
  `usr_SearchFamily` tinyint(3) DEFAULT NULL,
  `usr_Canvasser` tinyint(1) NOT NULL DEFAULT 0,
  `usr_ShowCart` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_ShowMap` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `usr_HomeDir` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_PastoralCare` tinyint(1) DEFAULT 0,
  `usr_MailChimp` tinyint(1) DEFAULT 0,
  `usr_MainDashboard` tinyint(1) DEFAULT 0,
  `usr_SeePrivacyData` tinyint(1) DEFAULT 0,
  `usr_GDRP_DPO` tinyint(1) DEFAULT 0,
  `usr_role_id` mediumint(11) UNSIGNED DEFAULT NULL,
  `usr_webDavKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_CurrentPath` varchar(1500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/',
  `usr_showMenuQuery` tinyint(1) NOT NULL DEFAULT 0,
  `usr_IsDeactivated` tinyint(1) DEFAULT 0,
  `usr_showTo` date NOT NULL DEFAULT '2019-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `user_usr`
--

INSERT INTO `user_usr` (`usr_per_ID`, `usr_Password`, `usr_NeedPasswordChange`, `usr_LastLogin`, `usr_LoginCount`, `usr_FailedLogins`, `usr_AddRecords`, `usr_EditRecords`, `usr_DeleteRecords`, `usr_MenuOptions`, `usr_ManageGroups`, `usr_Finance`, `usr_Notes`, `usr_Admin`, `usr_SearchLimit`, `usr_Style`, `usr_showPledges`, `usr_showPayments`, `usr_showSince`, `usr_defaultFY`, `usr_currentDeposit`, `usr_UserName`, `usr_EditSelf`, `usr_CalStart`, `usr_CalEnd`, `usr_CalNoSchool1`, `usr_CalNoSchool2`, `usr_CalNoSchool3`, `usr_CalNoSchool4`, `usr_CalNoSchool5`, `usr_CalNoSchool6`, `usr_CalNoSchool7`, `usr_CalNoSchool8`, `usr_SearchFamily`, `usr_Canvasser`, `usr_ShowCart`, `usr_ShowMap`, `usr_HomeDir`, `usr_PastoralCare`, `usr_MailChimp`, `usr_MainDashboard`, `usr_SeePrivacyData`, `usr_GDRP_DPO`, `usr_role_id`, `usr_webDavKey`, `usr_CurrentPath`, `usr_showMenuQuery`, `usr_IsDeactivated`, `usr_showTo`) VALUES
(1, '4bdf3fba58c956fc3991a1fde84929223f968e2853de596e49ae80a91499609b', 0, '2019-02-10 18:32:50', 450, 0, 1, 1, 1, 1, 1, 1, 1, 1, 5, 'skin-red-light', 1, 1, '2018-01-01', 22, 11, 'Admin', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 1, 1, 'private/userdir/5AA40E2E-D39F-4515-A7DD-83C3AF67CCE7/admin', 1, 1, 1, 1, 1, 1, '5AA40E2E-D39F-4515-A7DD-83C3AF67CCE7', '/', 1, 0, '2019-01-01'),
(5, '58ab7ab8722a65461f0186f3a70064d0562fbba12b62624f28caeb2aeef52b35', 1, '2019-02-01 08:09:24', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 'skin-yellow-light', 0, 0, '2018-01-01', 10, 0, 'toto', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'private/userdir/1925FC3D-2DA5-48B6-8B92-532B9A0505B1/toto', 0, 0, 0, 0, 0, 2, '1925FC3D-2DA5-48B6-8B92-532B9A0505B1', '/', 0, 0, '2019-01-01');

-- --------------------------------------------------------

--
-- Structure de la table `version_ver`
--

CREATE TABLE `version_ver` (
  `ver_ID` mediumint(9) UNSIGNED NOT NULL,
  `ver_version` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ver_update_start` datetime DEFAULT NULL,
  `ver_update_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `version_ver`
--

INSERT INTO `version_ver` (`ver_ID`, `ver_version`, `ver_update_start`, `ver_update_end`) VALUES
(1, '2.10.5', '2018-01-13 23:38:52', '2018-01-13 23:38:53'),
(2, '3.0.0', '2018-01-13 17:44:30', '2018-01-13 17:44:30'),
(3, '3.1.0', '2018-01-14 12:56:40', '2018-01-14 12:56:40'),
(6, '3.2.0', '2018-01-27 22:25:56', '2018-01-27 22:25:56'),
(7, '3.2.1', '2018-01-31 21:39:01', '2018-01-31 21:39:01'),
(10, '3.2.2', '2018-02-10 16:37:47', '2018-02-10 16:37:48'),
(11, '3.3.0', '2018-02-10 23:25:50', '2018-02-10 23:25:50'),
(12, '3.4.0', '2018-02-13 22:14:33', '2018-02-13 22:14:34'),
(13, '3.4.1', '2018-02-26 18:53:34', '2018-02-26 18:53:34'),
(14, '3.4.2', '2018-02-27 19:17:07', '2018-02-27 19:17:07'),
(15, '3.4.3', '2018-03-02 16:02:28', '2018-03-02 16:02:28'),
(16, '3.4.4', '2018-03-07 13:36:37', '2018-03-07 13:36:37'),
(18, '3.5.0', '2018-04-02 22:45:42', '2018-04-02 22:45:42'),
(19, '3.6.0', '2018-04-14 19:46:19', '2018-04-14 19:46:19'),
(20, '3.6.1', '2018-04-16 15:17:11', '2018-04-16 15:17:11'),
(21, '3.6.2', '2018-04-24 18:36:57', '2018-04-24 18:36:57'),
(25, '4.0.0', '2018-05-09 16:40:14', '2018-05-09 16:40:14'),
(26, '4.1.0', '2018-05-30 18:51:46', '2018-05-30 18:51:46'),
(28, '4.2.0', '2018-06-12 09:26:11', '2018-06-12 09:26:11'),
(29, '4.2.1', '2018-06-27 08:55:37', '2018-06-27 08:55:37'),
(31, '4.3.0', '2018-06-29 10:01:33', '2018-06-29 10:01:33'),
(32, '4.3.1', '2018-08-07 21:29:09', '2018-08-07 21:29:09'),
(33, '4.4.0', '2018-08-07 21:29:09', '2018-08-07 21:29:10'),
(34, '4.4.1', '2018-08-16 12:29:52', '2018-08-16 12:29:52'),
(35, '4.4.2', '2018-08-18 15:18:28', '2018-08-18 15:18:28'),
(36, '4.5.0', '2018-08-25 20:41:47', '2018-08-25 20:41:51'),
(37, '4.5.1', '2018-08-26 21:36:56', '2018-08-26 21:36:56'),
(38, '4.5.2', '2018-08-27 15:48:11', '2018-08-27 15:48:11'),
(39, '4.6.0', '2018-09-02 18:48:14', '2018-09-02 18:48:15'),
(40, '4.6.1', '2018-09-05 19:36:35', '2018-09-05 19:36:35'),
(41, '4.6.2', '2018-09-09 21:04:38', '2018-09-09 21:04:38'),
(42, '4.7.0', '2018-09-11 23:47:41', '2018-09-11 23:47:41'),
(43, '4.7.1', '2018-09-12 14:41:20', '2018-09-12 14:41:20'),
(44, '4.7.2', '2018-09-13 18:23:17', '2018-09-13 18:23:18'),
(45, '4.7.3', '2018-09-15 13:11:49', '2018-09-15 13:11:49'),
(46, '4.7.4', '2018-09-22 20:57:15', '2018-09-22 20:57:15'),
(47, '4.7.5', '2018-09-23 21:47:52', '2018-09-23 21:47:52'),
(48, '4.7.6', '2018-09-27 22:38:35', '2018-09-27 22:38:35'),
(49, '4.8.0', '2018-10-02 21:03:11', '2018-10-02 21:03:11'),
(50, '4.9.0', '2018-10-10 23:05:33', '2018-10-10 23:05:33'),
(51, '4.9.1', '2018-10-13 06:16:16', '2018-10-13 06:16:16'),
(52, '4.9.2', '2019-02-01 04:52:06', '2019-02-01 04:52:06'),
(53, '5.0.0', '2019-02-01 04:52:06', '2019-02-01 04:52:06'),
(54, '5.1.0', '2019-02-01 04:52:06', '2019-02-01 04:52:06'),
(55, '5.2.0', '2019-02-01 04:52:06', '2019-02-01 04:52:07'),
(56, '5.3.0', '2019-02-01 04:52:07', '2019-02-01 04:52:07'),
(57, '5.3.1', '2019-02-01 04:52:07', '2019-02-01 04:52:07'),
(60, '5.4.0', '2019-02-03 20:21:55', '2019-02-03 20:21:55'),
(61, '5.4.1', '2019-02-06 15:37:37', '2019-02-06 15:37:37'),
(62, '5.4.2', '2019-02-07 21:58:46', '2019-02-07 21:58:46');

-- --------------------------------------------------------

--
-- Structure de la table `volunteeropportunity_vol`
--

CREATE TABLE `volunteeropportunity_vol` (
  `vol_ID` mediumint(9) UNSIGNED NOT NULL,
  `vol_Order` int(3) NOT NULL DEFAULT 0,
  `vol_Active` enum('true','false') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'true',
  `vol_Name` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vol_Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `volunteeropportunity_vol`
--

INSERT INTO `volunteeropportunity_vol` (`vol_ID`, `vol_Order`, `vol_Active`, `vol_Name`, `vol_Description`) VALUES
(1, 1, 'true', 'Service informatique', 'eee'),
(2, 2, 'true', 'Sono', '');

-- --------------------------------------------------------

--
-- Structure de la vue `email_count`
--
DROP TABLE IF EXISTS `email_count`;

CREATE ALGORITHM=UNDEFINED DEFINER=`philippelo_demo_ecrm`@`localhost` SQL SECURITY DEFINER VIEW `email_count`  AS  select `email_list`.`email` AS `email`,count(0) AS `total` from `email_list` group by `email_list`.`email` ;

-- --------------------------------------------------------

--
-- Structure de la vue `email_list`
--
DROP TABLE IF EXISTS `email_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`philippelo_demo_ecrm`@`localhost` SQL SECURITY DEFINER VIEW `email_list`  AS  select `family_fam`.`fam_Email` AS `email`,'family' AS `type`,`family_fam`.`fam_ID` AS `id` from `family_fam` where `family_fam`.`fam_Email` is not null and `family_fam`.`fam_Email` <> '' union select `person_per`.`per_Email` AS `email`,'person_home' AS `type`,`person_per`.`per_ID` AS `id` from `person_per` where `person_per`.`per_Email` is not null and `person_per`.`per_Email` <> '' union select `person_per`.`per_WorkEmail` AS `email`,'person_work' AS `type`,`person_per`.`per_ID` AS `id` from `person_per` where `person_per`.`per_WorkEmail` is not null and `person_per`.`per_WorkEmail` <> '' ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `addressbookchanges`
--
ALTER TABLE `addressbookchanges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressbookid_synctoken` (`addressbookid`,`synctoken`);

--
-- Index pour la table `addressbooks`
--
ALTER TABLE `addressbooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `principaluri` (`principaluri`(100),`uri`(100));

--
-- Index pour la table `addressbookshare`
--
ALTER TABLE `addressbookshare`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `addressbooksid` (`addressbooksid`,`principaluri`),
  ADD UNIQUE KEY `principaluri` (`principaluri`(100));

--
-- Index pour la table `autopayment_aut`
--
ALTER TABLE `autopayment_aut`
  ADD PRIMARY KEY (`aut_ID`),
  ADD UNIQUE KEY `aut_ID` (`aut_ID`),
  ADD KEY `fk_aut_FamID` (`aut_FamID`),
  ADD KEY `fk_aut_Fund` (`aut_Fund`),
  ADD KEY `fk_aut_EditedBy` (`aut_EditedBy`);

--
-- Index pour la table `calendarchanges`
--
ALTER TABLE `calendarchanges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendarid_synctoken` (`calendarid`,`synctoken`);

--
-- Index pour la table `calendarinstances`
--
ALTER TABLE `calendarinstances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `principaluri` (`principaluri`,`uri`),
  ADD UNIQUE KEY `calendarid` (`calendarid`,`principaluri`),
  ADD UNIQUE KEY `calendarid_2` (`calendarid`,`share_href`);

--
-- Index pour la table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `calendarsubscriptions`
--
ALTER TABLE `calendarsubscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `principaluri` (`principaluri`,`uri`);

--
-- Index pour la table `canvassdata_can`
--
ALTER TABLE `canvassdata_can`
  ADD PRIMARY KEY (`can_ID`),
  ADD UNIQUE KEY `can_ID` (`can_ID`);

--
-- Index pour la table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `church_location`
--
ALTER TABLE `church_location`
  ADD PRIMARY KEY (`location_id`);

--
-- Index pour la table `church_location_person`
--
ALTER TABLE `church_location_person`
  ADD PRIMARY KEY (`location_id`,`person_id`);

--
-- Index pour la table `church_location_role`
--
ALTER TABLE `church_location_role`
  ADD PRIMARY KEY (`location_id`,`role_id`);

--
-- Index pour la table `ckeditor_templates`
--
ALTER TABLE `ckeditor_templates`
  ADD PRIMARY KEY (`cke_tmp_id`),
  ADD KEY `fk_cke_tmp_per_ID` (`cke_tmp_per_ID`);

--
-- Index pour la table `config_cfg`
--
ALTER TABLE `config_cfg`
  ADD PRIMARY KEY (`cfg_id`),
  ADD UNIQUE KEY `cfg_name` (`cfg_name`),
  ADD KEY `cfg_id` (`cfg_id`);

--
-- Index pour la table `deposit_dep`
--
ALTER TABLE `deposit_dep`
  ADD PRIMARY KEY (`dep_ID`);

--
-- Index pour la table `donateditem_di`
--
ALTER TABLE `donateditem_di`
  ADD PRIMARY KEY (`di_ID`),
  ADD UNIQUE KEY `di_ID` (`di_ID`);

--
-- Index pour la table `donationfund_fun`
--
ALTER TABLE `donationfund_fun`
  ADD PRIMARY KEY (`fun_ID`),
  ADD UNIQUE KEY `fun_ID` (`fun_ID`);

--
-- Index pour la table `egive_egv`
--
ALTER TABLE `egive_egv`
  ADD PRIMARY KEY (`egv_ID`),
  ADD KEY `fk_egv_famID` (`egv_famID`);

--
-- Index pour la table `eventcountnames_evctnm`
--
ALTER TABLE `eventcountnames_evctnm`
  ADD UNIQUE KEY `evctnm_countid` (`evctnm_countid`),
  ADD UNIQUE KEY `evctnm_eventtypeid` (`evctnm_eventtypeid`,`evctnm_countname`);

--
-- Index pour la table `eventcounts_evtcnt`
--
ALTER TABLE `eventcounts_evtcnt`
  ADD PRIMARY KEY (`evtcnt_eventid`,`evtcnt_countid`),
  ADD KEY `fk_evtcnt_countid` (`evtcnt_countid`);

--
-- Index pour la table `events_event`
--
ALTER TABLE `events_event`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `event_calendarid` (`event_calendarid`,`event_uri`),
  ADD KEY `calendarid_time` (`event_calendarid`);

--
-- Index pour la table `event_attend`
--
ALTER TABLE `event_attend`
  ADD PRIMARY KEY (`attend_id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`person_id`);

--
-- Index pour la table `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Index pour la table `family_custom`
--
ALTER TABLE `family_custom`
  ADD PRIMARY KEY (`fam_ID`);

--
-- Index pour la table `family_custom_master`
--
ALTER TABLE `family_custom_master`
  ADD PRIMARY KEY (`family_custom_id`);

--
-- Index pour la table `family_fam`
--
ALTER TABLE `family_fam`
  ADD PRIMARY KEY (`fam_ID`),
  ADD KEY `fam_ID` (`fam_ID`);

--
-- Index pour la table `fundraiser_fr`
--
ALTER TABLE `fundraiser_fr`
  ADD PRIMARY KEY (`fr_ID`),
  ADD UNIQUE KEY `fr_ID` (`fr_ID`);

--
-- Index pour la table `gdpr_infos`
--
ALTER TABLE `gdpr_infos`
  ADD PRIMARY KEY (`gdpr_info_id`);

--
-- Index pour la table `groupmembers`
--
ALTER TABLE `groupmembers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `principal_id` (`principal_id`,`member_id`);

--
-- Index pour la table `groupprop_4`
--
ALTER TABLE `groupprop_4`
  ADD PRIMARY KEY (`per_ID`),
  ADD UNIQUE KEY `per_ID` (`per_ID`);

--
-- Index pour la table `groupprop_master`
--
ALTER TABLE `groupprop_master`
  ADD PRIMARY KEY (`grp_mster_id`);

--
-- Index pour la table `group_grp`
--
ALTER TABLE `group_grp`
  ADD PRIMARY KEY (`grp_ID`),
  ADD UNIQUE KEY `grp_ID` (`grp_ID`),
  ADD KEY `grp_ID_2` (`grp_ID`);

--
-- Index pour la table `group_manager_person`
--
ALTER TABLE `group_manager_person`
  ADD PRIMARY KEY (`grp_mgr_per_id`),
  ADD KEY `fk_grp_mgr_per_person_ID` (`grp_mgr_per_person_ID`),
  ADD KEY `fk_grp_mgr_per_group_ID` (`grp_mgr_per_group_ID`);

--
-- Index pour la table `istlookup_lu`
--
ALTER TABLE `istlookup_lu`
  ADD PRIMARY KEY (`lu_fam_ID`);

--
-- Index pour la table `kioskassginment_kasm`
--
ALTER TABLE `kioskassginment_kasm`
  ADD PRIMARY KEY (`kasm_ID`),
  ADD UNIQUE KEY `kasm_ID` (`kasm_ID`);

--
-- Index pour la table `kioskdevice_kdev`
--
ALTER TABLE `kioskdevice_kdev`
  ADD PRIMARY KEY (`kdev_ID`),
  ADD UNIQUE KEY `kdev_ID` (`kdev_ID`);

--
-- Index pour la table `list_icon`
--
ALTER TABLE `list_icon`
  ADD PRIMARY KEY (`lst_ic_id`);

--
-- Index pour la table `locks`
--
ALTER TABLE `locks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`),
  ADD KEY `uri` (`uri`(100));

--
-- Index pour la table `menu_links`
--
ALTER TABLE `menu_links`
  ADD PRIMARY KEY (`linkId`),
  ADD KEY `fk_linkPersonId` (`linkPersonId`);

--
-- Index pour la table `multibuy_mb`
--
ALTER TABLE `multibuy_mb`
  ADD PRIMARY KEY (`mb_ID`),
  ADD UNIQUE KEY `mb_ID` (`mb_ID`);

--
-- Index pour la table `note_nte`
--
ALTER TABLE `note_nte`
  ADD PRIMARY KEY (`nte_ID`),
  ADD KEY `fk_nte_per_ID` (`nte_per_ID`),
  ADD KEY `fk_nte_fam_ID` (`nte_fam_ID`);

--
-- Index pour la table `note_nte_share`
--
ALTER TABLE `note_nte_share`
  ADD PRIMARY KEY (`nte_sh_id`),
  ADD KEY `fk_nte_note_ID` (`nte_sh_note_ID`),
  ADD KEY `fk_nte_share_from_person_ID` (`nte_sh_share_to_person_ID`),
  ADD KEY `fk_nte_share_from_family_ID` (`nte_sh_share_to_family_ID`);

--
-- Index pour la table `paddlenum_pn`
--
ALTER TABLE `paddlenum_pn`
  ADD PRIMARY KEY (`pn_ID`),
  ADD UNIQUE KEY `pn_ID` (`pn_ID`);

--
-- Index pour la table `pastoral_care`
--
ALTER TABLE `pastoral_care`
  ADD PRIMARY KEY (`pst_cr_id`),
  ADD KEY `fk_pst_cr_person_id` (`pst_cr_person_id`),
  ADD KEY `fk_pst_cr_pastor_id` (`pst_cr_pastor_id`),
  ADD KEY `fk_pst_cr_Type_id` (`pst_cr_Type_id`);

--
-- Index pour la table `pastoral_care_type`
--
ALTER TABLE `pastoral_care_type`
  ADD PRIMARY KEY (`pst_cr_tp_id`);

--
-- Index pour la table `person2group2role_p2g2r`
--
ALTER TABLE `person2group2role_p2g2r`
  ADD PRIMARY KEY (`p2g2r_per_ID`,`p2g2r_grp_ID`),
  ADD KEY `p2g2r_per_ID` (`p2g2r_per_ID`,`p2g2r_grp_ID`,`p2g2r_rle_ID`);

--
-- Index pour la table `person2volunteeropp_p2vo`
--
ALTER TABLE `person2volunteeropp_p2vo`
  ADD PRIMARY KEY (`p2vo_ID`),
  ADD UNIQUE KEY `p2vo_ID` (`p2vo_ID`),
  ADD KEY `fk_p2vo_vol_ID` (`p2vo_vol_ID`),
  ADD KEY `fk_p2vo_per_ID` (`p2vo_per_ID`);

--
-- Index pour la table `person_custom`
--
ALTER TABLE `person_custom`
  ADD PRIMARY KEY (`per_ID`);

--
-- Index pour la table `person_custom_master`
--
ALTER TABLE `person_custom_master`
  ADD PRIMARY KEY (`custom_id`);

--
-- Index pour la table `person_per`
--
ALTER TABLE `person_per`
  ADD PRIMARY KEY (`per_ID`),
  ADD KEY `per_ID` (`per_ID`);

--
-- Index pour la table `pledge_plg`
--
ALTER TABLE `pledge_plg`
  ADD PRIMARY KEY (`plg_plgID`);

--
-- Index pour la table `principals`
--
ALTER TABLE `principals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uri` (`uri`);

--
-- Index pour la table `propertystorage`
--
ALTER TABLE `propertystorage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `path_property` (`path`(600),`name`);

--
-- Index pour la table `propertytype_prt`
--
ALTER TABLE `propertytype_prt`
  ADD PRIMARY KEY (`prt_ID`),
  ADD UNIQUE KEY `prt_ID` (`prt_ID`),
  ADD KEY `prt_ID_2` (`prt_ID`);

--
-- Index pour la table `property_pro`
--
ALTER TABLE `property_pro`
  ADD PRIMARY KEY (`pro_ID`),
  ADD UNIQUE KEY `pro_ID` (`pro_ID`),
  ADD KEY `pro_ID_2` (`pro_ID`);

--
-- Index pour la table `queryparameteroptions_qpo`
--
ALTER TABLE `queryparameteroptions_qpo`
  ADD PRIMARY KEY (`qpo_ID`),
  ADD UNIQUE KEY `qpo_ID` (`qpo_ID`);

--
-- Index pour la table `queryparameters_qrp`
--
ALTER TABLE `queryparameters_qrp`
  ADD PRIMARY KEY (`qrp_ID`),
  ADD UNIQUE KEY `qrp_ID` (`qrp_ID`),
  ADD KEY `qrp_ID_2` (`qrp_ID`),
  ADD KEY `qrp_qry_ID` (`qrp_qry_ID`);

--
-- Index pour la table `query_qry`
--
ALTER TABLE `query_qry`
  ADD PRIMARY KEY (`qry_ID`),
  ADD UNIQUE KEY `qry_ID` (`qry_ID`),
  ADD KEY `qry_ID_2` (`qry_ID`);

--
-- Index pour la table `query_type`
--
ALTER TABLE `query_type`
  ADD PRIMARY KEY (`qry_type_id`);

--
-- Index pour la table `record2property_r2p`
--
ALTER TABLE `record2property_r2p`
  ADD PRIMARY KEY (`r2p_id`);

--
-- Index pour la table `result_res`
--
ALTER TABLE `result_res`
  ADD PRIMARY KEY (`res_ID`);

--
-- Index pour la table `schedulingobjects`
--
ALTER TABLE `schedulingobjects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`token`);

--
-- Index pour la table `userconfig_ucfg`
--
ALTER TABLE `userconfig_ucfg`
  ADD PRIMARY KEY (`ucfg_per_id`,`ucfg_id`);

--
-- Index pour la table `userrole_usrrol`
--
ALTER TABLE `userrole_usrrol`
  ADD PRIMARY KEY (`usrrol_id`);

--
-- Index pour la table `user_usr`
--
ALTER TABLE `user_usr`
  ADD PRIMARY KEY (`usr_per_ID`),
  ADD UNIQUE KEY `usr_UserName` (`usr_UserName`),
  ADD UNIQUE KEY `usr_webDavKey` (`usr_webDavKey`),
  ADD KEY `usr_per_ID` (`usr_per_ID`),
  ADD KEY `fk_usr_role_id` (`usr_role_id`);

--
-- Index pour la table `version_ver`
--
ALTER TABLE `version_ver`
  ADD PRIMARY KEY (`ver_ID`),
  ADD UNIQUE KEY `ver_version` (`ver_version`);

--
-- Index pour la table `volunteeropportunity_vol`
--
ALTER TABLE `volunteeropportunity_vol`
  ADD PRIMARY KEY (`vol_ID`),
  ADD UNIQUE KEY `vol_ID` (`vol_ID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `addressbookchanges`
--
ALTER TABLE `addressbookchanges`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `addressbooks`
--
ALTER TABLE `addressbooks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `addressbookshare`
--
ALTER TABLE `addressbookshare`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `autopayment_aut`
--
ALTER TABLE `autopayment_aut`
  MODIFY `aut_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `calendarchanges`
--
ALTER TABLE `calendarchanges`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT pour la table `calendarinstances`
--
ALTER TABLE `calendarinstances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `calendars`
--
ALTER TABLE `calendars`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `calendarsubscriptions`
--
ALTER TABLE `calendarsubscriptions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `canvassdata_can`
--
ALTER TABLE `canvassdata_can`
  MODIFY `can_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `ckeditor_templates`
--
ALTER TABLE `ckeditor_templates`
  MODIFY `cke_tmp_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `deposit_dep`
--
ALTER TABLE `deposit_dep`
  MODIFY `dep_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `donateditem_di`
--
ALTER TABLE `donateditem_di`
  MODIFY `di_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `donationfund_fun`
--
ALTER TABLE `donationfund_fun`
  MODIFY `fun_ID` tinyint(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `egive_egv`
--
ALTER TABLE `egive_egv`
  MODIFY `egv_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eventcountnames_evctnm`
--
ALTER TABLE `eventcountnames_evctnm`
  MODIFY `evctnm_countid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `events_event`
--
ALTER TABLE `events_event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT pour la table `event_attend`
--
ALTER TABLE `event_attend`
  MODIFY `attend_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `family_custom_master`
--
ALTER TABLE `family_custom_master`
  MODIFY `family_custom_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `family_fam`
--
ALTER TABLE `family_fam`
  MODIFY `fam_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `fundraiser_fr`
--
ALTER TABLE `fundraiser_fr`
  MODIFY `fr_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `gdpr_infos`
--
ALTER TABLE `gdpr_infos`
  MODIFY `gdpr_info_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `groupmembers`
--
ALTER TABLE `groupmembers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groupprop_master`
--
ALTER TABLE `groupprop_master`
  MODIFY `grp_mster_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `group_grp`
--
ALTER TABLE `group_grp`
  MODIFY `grp_ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `group_manager_person`
--
ALTER TABLE `group_manager_person`
  MODIFY `grp_mgr_per_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `kioskassginment_kasm`
--
ALTER TABLE `kioskassginment_kasm`
  MODIFY `kasm_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kioskdevice_kdev`
--
ALTER TABLE `kioskdevice_kdev`
  MODIFY `kdev_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `list_icon`
--
ALTER TABLE `list_icon`
  MODIFY `lst_ic_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `locks`
--
ALTER TABLE `locks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `menu_links`
--
ALTER TABLE `menu_links`
  MODIFY `linkId` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `multibuy_mb`
--
ALTER TABLE `multibuy_mb`
  MODIFY `mb_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `note_nte`
--
ALTER TABLE `note_nte`
  MODIFY `nte_ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT pour la table `note_nte_share`
--
ALTER TABLE `note_nte_share`
  MODIFY `nte_sh_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `paddlenum_pn`
--
ALTER TABLE `paddlenum_pn`
  MODIFY `pn_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pastoral_care`
--
ALTER TABLE `pastoral_care`
  MODIFY `pst_cr_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `pastoral_care_type`
--
ALTER TABLE `pastoral_care_type`
  MODIFY `pst_cr_tp_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `person2volunteeropp_p2vo`
--
ALTER TABLE `person2volunteeropp_p2vo`
  MODIFY `p2vo_ID` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `person_custom_master`
--
ALTER TABLE `person_custom_master`
  MODIFY `custom_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `person_per`
--
ALTER TABLE `person_per`
  MODIFY `per_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `pledge_plg`
--
ALTER TABLE `pledge_plg`
  MODIFY `plg_plgID` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `principals`
--
ALTER TABLE `principals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `propertystorage`
--
ALTER TABLE `propertystorage`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `propertytype_prt`
--
ALTER TABLE `propertytype_prt`
  MODIFY `prt_ID` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `property_pro`
--
ALTER TABLE `property_pro`
  MODIFY `pro_ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `queryparameteroptions_qpo`
--
ALTER TABLE `queryparameteroptions_qpo`
  MODIFY `qpo_ID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `queryparameters_qrp`
--
ALTER TABLE `queryparameters_qrp`
  MODIFY `qrp_ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT pour la table `query_qry`
--
ALTER TABLE `query_qry`
  MODIFY `qry_ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT pour la table `query_type`
--
ALTER TABLE `query_type`
  MODIFY `qry_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `record2property_r2p`
--
ALTER TABLE `record2property_r2p`
  MODIFY `r2p_id` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `result_res`
--
ALTER TABLE `result_res`
  MODIFY `res_ID` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `schedulingobjects`
--
ALTER TABLE `schedulingobjects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `userrole_usrrol`
--
ALTER TABLE `userrole_usrrol`
  MODIFY `usrrol_id` mediumint(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `version_ver`
--
ALTER TABLE `version_ver`
  MODIFY `ver_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT pour la table `volunteeropportunity_vol`
--
ALTER TABLE `volunteeropportunity_vol`
  MODIFY `vol_ID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `addressbookshare`
--
ALTER TABLE `addressbookshare`
  ADD CONSTRAINT `fk_addressbooksid` FOREIGN KEY (`addressbooksid`) REFERENCES `addressbooks` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `autopayment_aut`
--
ALTER TABLE `autopayment_aut`
  ADD CONSTRAINT `fk_aut_EditedBy` FOREIGN KEY (`aut_EditedBy`) REFERENCES `person_per` (`per_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_aut_FamID` FOREIGN KEY (`aut_FamID`) REFERENCES `family_fam` (`fam_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_aut_Fund` FOREIGN KEY (`aut_Fund`) REFERENCES `donationfund_fun` (`fun_ID`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ckeditor_templates`
--
ALTER TABLE `ckeditor_templates`
  ADD CONSTRAINT `fk_cke_tmp_per_ID` FOREIGN KEY (`cke_tmp_per_ID`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `egive_egv`
--
ALTER TABLE `egive_egv`
  ADD CONSTRAINT `fk_egv_famID` FOREIGN KEY (`egv_famID`) REFERENCES `family_fam` (`fam_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `eventcountnames_evctnm`
--
ALTER TABLE `eventcountnames_evctnm`
  ADD CONSTRAINT `fk_evctnm_eventtypeid` FOREIGN KEY (`evctnm_eventtypeid`) REFERENCES `event_types` (`type_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `eventcounts_evtcnt`
--
ALTER TABLE `eventcounts_evtcnt`
  ADD CONSTRAINT `fk_evtcnt_countid` FOREIGN KEY (`evtcnt_countid`) REFERENCES `eventcountnames_evctnm` (`evctnm_countid`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evtcnt_event_ID` FOREIGN KEY (`evtcnt_eventid`) REFERENCES `events_event` (`event_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `event_attend`
--
ALTER TABLE `event_attend`
  ADD CONSTRAINT `fk_attend_event_ID` FOREIGN KEY (`event_id`) REFERENCES `events_event` (`event_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `group_manager_person`
--
ALTER TABLE `group_manager_person`
  ADD CONSTRAINT `fk_grp_mgr_per_group_ID` FOREIGN KEY (`grp_mgr_per_group_ID`) REFERENCES `group_grp` (`grp_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grp_mgr_per_person_ID` FOREIGN KEY (`grp_mgr_per_person_ID`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `menu_links`
--
ALTER TABLE `menu_links`
  ADD CONSTRAINT `fk_linkPersonId` FOREIGN KEY (`linkPersonId`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_nte`
--
ALTER TABLE `note_nte`
  ADD CONSTRAINT `fk_nte_fam_ID` FOREIGN KEY (`nte_fam_ID`) REFERENCES `family_fam` (`fam_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nte_per_ID` FOREIGN KEY (`nte_per_ID`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note_nte_share`
--
ALTER TABLE `note_nte_share`
  ADD CONSTRAINT `fk_nte_note_ID` FOREIGN KEY (`nte_sh_note_ID`) REFERENCES `note_nte` (`nte_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nte_share_from_family_ID` FOREIGN KEY (`nte_sh_share_to_family_ID`) REFERENCES `family_fam` (`fam_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nte_share_from_person_ID` FOREIGN KEY (`nte_sh_share_to_person_ID`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pastoral_care`
--
ALTER TABLE `pastoral_care`
  ADD CONSTRAINT `fk_pst_cr_Type_id` FOREIGN KEY (`pst_cr_Type_id`) REFERENCES `pastoral_care_type` (`pst_cr_tp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pst_cr_pastor_id` FOREIGN KEY (`pst_cr_pastor_id`) REFERENCES `person_per` (`per_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pst_cr_person_id` FOREIGN KEY (`pst_cr_person_id`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `person2volunteeropp_p2vo`
--
ALTER TABLE `person2volunteeropp_p2vo`
  ADD CONSTRAINT `fk_p2vo_per_ID` FOREIGN KEY (`p2vo_per_ID`) REFERENCES `person_per` (`per_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_p2vo_vol_ID` FOREIGN KEY (`p2vo_vol_ID`) REFERENCES `volunteeropportunity_vol` (`vol_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_usr`
--
ALTER TABLE `user_usr`
  ADD CONSTRAINT `fk_usr_role_id` FOREIGN KEY (`usr_role_id`) REFERENCES `userrole_usrrol` (`usrrol_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
