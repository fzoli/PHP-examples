-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Hoszt: localhost
-- Létrehozás ideje: 2011. ápr. 10. 14:46
-- Szerver verzió: 5.1.49
-- PHP verzió: 5.3.3-1ubuntu9.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Adatbázis: `film`
--
CREATE DATABASE `film` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `film`;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `extra`
--

CREATE TABLE IF NOT EXISTS `extra` (
  `lemez` int(10) unsigned NOT NULL,
  `extra` int(10) unsigned NOT NULL,
  PRIMARY KEY (`lemez`,`extra`),
  KEY `extra` (`extra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `extra`
--


-- --------------------------------------------------------

--
-- Tábla szerkezet: `felirat`
--

CREATE TABLE IF NOT EXISTS `felirat` (
  `lemez` int(10) unsigned NOT NULL,
  `felirat` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`lemez`,`felirat`),
  KEY `felirat` (`felirat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `felirat`
--

INSERT INTO `felirat` (`lemez`, `felirat`) VALUES
(1, 1),
(1, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `feliratok`
--

CREATE TABLE IF NOT EXISTS `feliratok` (
  `azon` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nyelv` int(10) unsigned NOT NULL,
  `nema` tinyint(1) NOT NULL,
  `kommentar` tinyint(1) NOT NULL,
  PRIMARY KEY (`nyelv`,`nema`,`kommentar`),
  UNIQUE KEY `azon` (`azon`),
  KEY `nyelv` (`nyelv`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- A tábla adatainak kiíratása `feliratok`
--

INSERT INTO `feliratok` (`azon`, `nyelv`, `nema`, `kommentar`) VALUES
(1, 24, 0, 0),
(2, 25, 0, 0),
(3, 26, 0, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `film`
--

CREATE TABLE IF NOT EXISTS `film` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cim` varchar(40) NOT NULL,
  `angol_cim` varchar(40) NOT NULL,
  `nemzetiseg` int(10) unsigned NOT NULL,
  `hossz` tinyint(3) unsigned NOT NULL,
  `gyart_ev` year(4) NOT NULL,
  `korhatar` int(10) unsigned NOT NULL,
  `leiras` text NOT NULL,
  `borito` varchar(37) NOT NULL,
  `letrehozva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`azon`),
  KEY `korhatar` (`korhatar`),
  KEY `nemzetiseg` (`nemzetiseg`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- A tábla adatainak kiíratása `film`
--

INSERT INTO `film` (`azon`, `cim`, `angol_cim`, `nemzetiseg`, `hossz`, `gyart_ev`, `korhatar`, `leiras`, `borito`, `letrehozva`) VALUES
(1, 'Avatar', 'Avatar', 27, 166, 2009, 1, 'Egy deréktól lefelé megbénult háborús veterán a távoli Pandorára utazik. A bolygó lakói, a Na''vik az emberhez hasonló faj - de nyelvük és kultúrájuk felfoghatatlanul különbözik a miénktől. Ebben a gyönyörű és halálos veszélyeket rejtő világban a földieknek nagyon nagy árat kell fizetniük a túlélésért.\r\nDe nagyon nagy lehetőséghez is jutnak: a régi agyuk megőrzésével új testet ölthetnek, és az új testben, egy idegen lény szemével figyelhetik magukat és a körülöttük lévő felfoghatatlan, vad világot.\r\nA veterán azonban más céllal érkezett. Az új test új, titkos feladatot is jelent számára, amit mindenáron végre kell hajtania.', 'efdd1a1c62642b639916b1349827416c.jpg', '2010-07-19 14:06:31');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `hang`
--

CREATE TABLE IF NOT EXISTS `hang` (
  `lemez` int(10) unsigned NOT NULL,
  `hang` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`lemez`,`hang`),
  KEY `hang` (`hang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `hang`
--

INSERT INTO `hang` (`lemez`, `hang`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `hangok`
--

CREATE TABLE IF NOT EXISTS `hangok` (
  `azon` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nyelv` int(10) unsigned NOT NULL,
  `csatorna` int(10) unsigned NOT NULL,
  `kodolas` int(10) unsigned NOT NULL,
  PRIMARY KEY (`nyelv`,`csatorna`,`kodolas`),
  UNIQUE KEY `azon` (`azon`),
  KEY `csatorna` (`csatorna`),
  KEY `kodolas` (`kodolas`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- A tábla adatainak kiíratása `hangok`
--

INSERT INTO `hangok` (`azon`, `nyelv`, `csatorna`, `kodolas`) VALUES
(1, 24, 28, 29),
(2, 25, 28, 29);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `kategoria`
--

CREATE TABLE IF NOT EXISTS `kategoria` (
  `azon` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `megnev` varchar(20) NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `megnev` (`megnev`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- A tábla adatainak kiíratása `kategoria`
--

INSERT INTO `kategoria` (`azon`, `megnev`) VALUES
(12, 'állapot'),
(5, 'csatorna'),
(11, 'extra'),
(8, 'felbontás'),
(14, 'film beszerzés'),
(7, 'képarány'),
(6, 'kódolás'),
(1, 'korhatár'),
(2, 'lemeztípus'),
(3, 'műfaj'),
(13, 'nemzetiség'),
(4, 'nyelv'),
(9, 'stáb'),
(10, 'szerep');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `kep`
--

CREATE TABLE IF NOT EXISTS `kep` (
  `lemez` int(10) unsigned NOT NULL,
  `kep` tinyint(3) unsigned NOT NULL,
  `nezet_db` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`lemez`,`kep`),
  KEY `kep` (`kep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `kep`
--

INSERT INTO `kep` (`lemez`, `kep`, `nezet_db`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `kepek`
--

CREATE TABLE IF NOT EXISTS `kepek` (
  `azon` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `keparany` int(10) unsigned NOT NULL,
  `felbontas` int(10) unsigned NOT NULL,
  `szines` tinyint(1) NOT NULL,
  `3D` tinyint(1) NOT NULL,
  PRIMARY KEY (`keparany`,`felbontas`,`szines`,`3D`),
  UNIQUE KEY `azon` (`azon`),
  KEY `keparany` (`keparany`),
  KEY `felbontas` (`felbontas`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- A tábla adatainak kiíratása `kepek`
--

INSERT INTO `kepek` (`azon`, `keparany`, `felbontas`, `szines`, `3D`) VALUES
(1, 22, 23, 1, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `lemez`
--

CREATE TABLE IF NOT EXISTS `lemez` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `film` int(10) unsigned NOT NULL,
  `tipus` int(10) unsigned NOT NULL,
  `lemez_db` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `menu` tinyint(1) NOT NULL,
  `film_beszerzes` int(11) unsigned NOT NULL,
  `bovitett` tinyint(1) NOT NULL,
  PRIMARY KEY (`azon`),
  KEY `film` (`film`),
  KEY `tipus` (`tipus`),
  KEY `film_beszerzes` (`film_beszerzes`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- A tábla adatainak kiíratása `lemez`
--

INSERT INTO `lemez` (`azon`, `film`, `tipus`, `lemez_db`, `menu`, `film_beszerzes`, `bovitett`) VALUES
(1, 1, 20, 1, 1, 31, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `lista`
--

CREATE TABLE IF NOT EXISTS `lista` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `megnev` varchar(50) NOT NULL,
  `kategoria` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`azon`),
  UNIQUE KEY `megnev` (`megnev`),
  KEY `kategoria` (`kategoria`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

--
-- A tábla adatainak kiíratása `lista`
--

INSERT INTO `lista` (`azon`, `megnev`, `kategoria`) VALUES
(1, '16', 1),
(2, 'sci-fi', 3),
(3, 'Rendező', 9),
(4, 'Forgatókönyvíró', 9),
(5, 'Operatőr', 9),
(6, 'Zene', 9),
(7, 'Producer', 9),
(8, 'Executive producer', 9),
(9, 'Látványtervező', 9),
(10, 'Vágó', 9),
(11, 'Jake Sully', 10),
(12, 'Dr. Grace Augustine', 10),
(13, 'Neytiri', 10),
(14, 'Miles Quaritch', 10),
(15, 'Selfridge', 10),
(16, 'Trudy Chacon', 10),
(17, 'Norm Spellman', 10),
(18, 'Moha', 10),
(19, 'Dr. Max Patel', 10),
(20, 'DVD', 2),
(21, 'bent', 12),
(22, '4:3', 7),
(23, '720 x 576', 8),
(24, 'magyar', 4),
(25, 'angol', 4),
(26, 'török', 4),
(27, 'amerikai', 13),
(28, '5.1', 5),
(29, 'AC3', 6),
(30, 'gyári', 14),
(31, 'rippelt', 14),
(32, 'mozifelvétel', 14),
(33, 'DTS', 6);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `mufaj`
--

CREATE TABLE IF NOT EXISTS `mufaj` (
  `film` int(10) unsigned NOT NULL,
  `mufaj` int(10) unsigned NOT NULL,
  PRIMARY KEY (`film`,`mufaj`),
  KEY `mufaj` (`mufaj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `mufaj`
--

INSERT INTO `mufaj` (`film`, `mufaj`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `peldany`
--

CREATE TABLE IF NOT EXISTS `peldany` (
  `azon` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `lemez_sorszam` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `allapot` int(10) unsigned NOT NULL,
  `lemez` int(10) unsigned NOT NULL,
  `eredeti` tinyint(1) NOT NULL,
  PRIMARY KEY (`azon`),
  KEY `allapot` (`allapot`),
  KEY `lemez` (`lemez`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=657 ;

--
-- A tábla adatainak kiíratása `peldany`
--

INSERT INTO `peldany` (`azon`, `lemez_sorszam`, `allapot`, `lemez`, `eredeti`) VALUES
(656, 1, 21, 1, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `proba`
--

CREATE TABLE IF NOT EXISTS `proba` (
  `proba` enum('egy','kettő','három') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `proba`
--

INSERT INTO `proba` (`proba`) VALUES
('kettő');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `snapshot`
--

CREATE TABLE IF NOT EXISTS `snapshot` (
  `film` int(10) unsigned NOT NULL,
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(37) NOT NULL,
  PRIMARY KEY (`azon`),
  KEY `film` (`film`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- A tábla adatainak kiíratása `snapshot`
--

INSERT INTO `snapshot` (`film`, `azon`, `file`) VALUES
(1, 1, '6da7542795519827b7a4754d244ffca2.jpg'),
(1, 2, '039619838ebe95c46d27e963c897c7cd.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `stab`
--

CREATE TABLE IF NOT EXISTS `stab` (
  `film` int(10) unsigned NOT NULL,
  `szemely` int(10) unsigned NOT NULL,
  `munka` int(10) unsigned NOT NULL,
  PRIMARY KEY (`film`,`szemely`,`munka`),
  KEY `szemely` (`szemely`),
  KEY `munka` (`munka`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `stab`
--

INSERT INTO `stab` (`film`, `szemely`, `munka`) VALUES
(1, 1, 3),
(1, 1, 4),
(1, 1, 7),
(1, 2, 5),
(1, 3, 6),
(1, 4, 6),
(1, 5, 7),
(1, 6, 8),
(1, 7, 9),
(1, 8, 10),
(1, 9, 10),
(1, 24, 9),
(1, 25, 9);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `szemely`
--

CREATE TABLE IF NOT EXISTS `szemely` (
  `azon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nev` varchar(40) NOT NULL,
  `nem` tinyint(1) NOT NULL,
  `szul_datum` year(4) NOT NULL,
  PRIMARY KEY (`azon`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

--
-- A tábla adatainak kiíratása `szemely`
--

INSERT INTO `szemely` (`azon`, `nev`, `nem`, `szul_datum`) VALUES
(1, 'James Cameron', 1, 1954),
(2, 'Mauro Fiore', 1, 1964),
(3, 'Simon Franglen', 1, 0000),
(4, 'James Horner', 1, 1953),
(5, 'Jon Landau', 1, 1947),
(6, 'Colin Wilson', 1, 1931),
(7, 'Kim Sinclair', 0, 1954),
(8, 'John Refoua', 1, 0000),
(9, 'Stephen E. Rivkin', 1, 0000),
(10, 'Sam Worthington', 1, 1976),
(11, 'Sigourney Weaver', 0, 1949),
(12, 'Zoe Saldana', 0, 1978),
(13, 'Stephen Lang', 1, 1952),
(14, 'Giovanni Ribisi', 1, 1974),
(15, 'Michelle Rodriguez', 0, 1978),
(16, 'Joel Moore', 1, 1977),
(17, 'CCH Pounder', 0, 1952),
(18, 'Dileep Rao', 1, 0000),
(19, 'Széles Tamás', 1, 1973),
(20, 'Menszátor Magdolna', 0, 1951),
(21, 'Pikali Gerda', 0, 1978),
(23, 'Rajkai Zoltán', 1, 1969),
(24, 'Rick Carter', 1, 1952),
(25, 'Robert Stromberg', 1, 0000),
(27, 'Kőszegi Ákos', 1, 1960);

-- --------------------------------------------------------

--
-- Tábla szerkezet: `szerep`
--

CREATE TABLE IF NOT EXISTS `szerep` (
  `film` int(10) unsigned NOT NULL,
  `szemely` int(10) unsigned NOT NULL,
  `szerep` int(10) unsigned NOT NULL,
  `szinkron` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`film`,`szerep`),
  KEY `személy` (`szemely`),
  KEY `szinkron` (`szinkron`),
  KEY `szerep` (`szerep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `szerep`
--

INSERT INTO `szerep` (`film`, `szemely`, `szerep`, `szinkron`) VALUES
(1, 10, 11, 19),
(1, 11, 12, 20),
(1, 12, 13, 21),
(1, 13, 14, 27),
(1, 14, 15, 23),
(1, 15, 16, NULL),
(1, 16, 17, NULL),
(1, 17, 18, NULL),
(1, 18, 19, NULL);

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `extra`
--
ALTER TABLE `extra`
  ADD CONSTRAINT `extra_ibfk_1` FOREIGN KEY (`lemez`) REFERENCES `lemez` (`azon`),
  ADD CONSTRAINT `extra_ibfk_2` FOREIGN KEY (`extra`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `felirat`
--
ALTER TABLE `felirat`
  ADD CONSTRAINT `felirat_ibfk_1` FOREIGN KEY (`lemez`) REFERENCES `lemez` (`azon`),
  ADD CONSTRAINT `felirat_ibfk_2` FOREIGN KEY (`felirat`) REFERENCES `feliratok` (`azon`);

--
-- Megkötések a táblához `feliratok`
--
ALTER TABLE `feliratok`
  ADD CONSTRAINT `feliratok_ibfk_1` FOREIGN KEY (`nyelv`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `film`
--
ALTER TABLE `film`
  ADD CONSTRAINT `film_ibfk_1` FOREIGN KEY (`korhatar`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `film_ibfk_2` FOREIGN KEY (`nemzetiseg`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `hang`
--
ALTER TABLE `hang`
  ADD CONSTRAINT `hang_ibfk_1` FOREIGN KEY (`lemez`) REFERENCES `lemez` (`azon`),
  ADD CONSTRAINT `hang_ibfk_2` FOREIGN KEY (`hang`) REFERENCES `hangok` (`azon`);

--
-- Megkötések a táblához `hangok`
--
ALTER TABLE `hangok`
  ADD CONSTRAINT `hangok_ibfk_1` FOREIGN KEY (`nyelv`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `hangok_ibfk_2` FOREIGN KEY (`csatorna`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `hangok_ibfk_3` FOREIGN KEY (`kodolas`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `kep`
--
ALTER TABLE `kep`
  ADD CONSTRAINT `kep_ibfk_1` FOREIGN KEY (`lemez`) REFERENCES `lemez` (`azon`),
  ADD CONSTRAINT `kep_ibfk_2` FOREIGN KEY (`kep`) REFERENCES `kepek` (`azon`);

--
-- Megkötések a táblához `kepek`
--
ALTER TABLE `kepek`
  ADD CONSTRAINT `kepek_ibfk_1` FOREIGN KEY (`keparany`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `kepek_ibfk_2` FOREIGN KEY (`felbontas`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `lemez`
--
ALTER TABLE `lemez`
  ADD CONSTRAINT `lemez_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`azon`),
  ADD CONSTRAINT `lemez_ibfk_2` FOREIGN KEY (`tipus`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `lemez_ibfk_3` FOREIGN KEY (`film_beszerzes`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `lista`
--
ALTER TABLE `lista`
  ADD CONSTRAINT `lista_ibfk_1` FOREIGN KEY (`kategoria`) REFERENCES `kategoria` (`azon`);

--
-- Megkötések a táblához `mufaj`
--
ALTER TABLE `mufaj`
  ADD CONSTRAINT `mufaj_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`azon`),
  ADD CONSTRAINT `mufaj_ibfk_2` FOREIGN KEY (`mufaj`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `peldany`
--
ALTER TABLE `peldany`
  ADD CONSTRAINT `peldany_ibfk_1` FOREIGN KEY (`allapot`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `peldany_ibfk_2` FOREIGN KEY (`lemez`) REFERENCES `lemez` (`azon`);

--
-- Megkötések a táblához `snapshot`
--
ALTER TABLE `snapshot`
  ADD CONSTRAINT `snapshot_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`azon`);

--
-- Megkötések a táblához `stab`
--
ALTER TABLE `stab`
  ADD CONSTRAINT `stab_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`azon`),
  ADD CONSTRAINT `stab_ibfk_2` FOREIGN KEY (`szemely`) REFERENCES `szemely` (`azon`),
  ADD CONSTRAINT `stab_ibfk_3` FOREIGN KEY (`munka`) REFERENCES `lista` (`azon`);

--
-- Megkötések a táblához `szerep`
--
ALTER TABLE `szerep`
  ADD CONSTRAINT `szerep_ibfk_1` FOREIGN KEY (`film`) REFERENCES `film` (`azon`),
  ADD CONSTRAINT `szerep_ibfk_3` FOREIGN KEY (`szinkron`) REFERENCES `szemely` (`azon`),
  ADD CONSTRAINT `szerep_ibfk_4` FOREIGN KEY (`szerep`) REFERENCES `lista` (`azon`),
  ADD CONSTRAINT `szerep_ibfk_5` FOREIGN KEY (`szemely`) REFERENCES `szemely` (`azon`);
--
-- Adatbázis: `user`
--
CREATE DATABASE `user` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `user`;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `jog`
--

CREATE TABLE IF NOT EXISTS `jog` (
  `azon` int(2) unsigned NOT NULL,
  `read` tinyint(1) NOT NULL,
  `change` tinyint(1) NOT NULL,
  `delette` tinyint(1) NOT NULL,
  `jog_change` tinyint(1) NOT NULL,
  `vedett_change` tinyint(1) NOT NULL,
  `rootjog_change` tinyint(1) NOT NULL,
  `elnev` varchar(15) NOT NULL,
  PRIMARY KEY (`azon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `jog`
--

INSERT INTO `jog` (`azon`, `read`, `change`, `delette`, `jog_change`, `vedett_change`, `rootjog_change`, `elnev`) VALUES
(0, 0, 0, 0, 0, 0, 0, 'felhasználó'),
(1, 1, 1, 0, 0, 0, 0, 'moderátor'),
(2, 1, 1, 1, 1, 0, 0, 'admin'),
(3, 1, 1, 1, 1, 1, 1, 'root');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `logon`
--

CREATE TABLE IF NOT EXISTS `logon` (
  `user` varchar(15) NOT NULL,
  `key` varchar(40) NOT NULL,
  `failed` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `expire` datetime NOT NULL,
  PRIMARY KEY (`user`,`key`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `logon`
--


-- --------------------------------------------------------

--
-- Tábla szerkezet: `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(88) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `key` varchar(40) NOT NULL,
  `temp_id` varchar(88) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `data` text NOT NULL,
  `azon` varchar(15) DEFAULT NULL,
  `last_idchange` bigint(11) NOT NULL,
  `expire` bigint(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `temp_id` (`temp_id`),
  KEY `azon` (`azon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `sessions`
--


--
-- Eseményindítók `sessions`
--
DROP TRIGGER IF EXISTS `setLastUserRequest`;
DELIMITER //
CREATE TRIGGER `setLastUserRequest` BEFORE UPDATE ON `sessions`
 FOR EACH ROW BEGIN

		UPDATE `user` SET `utolso_keres` = now() WHERE `azon` = NEW.`azon` LIMIT 1;

	END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `azon` varchar(15) NOT NULL,
  `jelszo` varchar(32) NOT NULL,
  `nev` varchar(20) NOT NULL,
  `sz_datum` date NOT NULL,
  `nem` tinyint(1) NOT NULL,
  `email_cim` varchar(35) NOT NULL,
  `email_publikus` tinyint(1) NOT NULL,
  `info` text NOT NULL,
  `avatar` varchar(36) NOT NULL,
  `jog` int(10) unsigned NOT NULL DEFAULT '0',
  `vedett` tinyint(1) NOT NULL DEFAULT '0',
  `utolso_keres` datetime NOT NULL,
  `probalkozas` tinyint(2) NOT NULL DEFAULT '10',
  `tiltott` tinyint(1) NOT NULL DEFAULT '0',
  `bann_lejar` datetime NOT NULL,
  PRIMARY KEY (`azon`),
  KEY `jog` (`jog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `user`
--

INSERT INTO `user` (`azon`, `jelszo`, `nev`, `sz_datum`, `nem`, `email_cim`, `email_publikus`, `info`, `avatar`, `jog`, `vedett`, `utolso_keres`, `probalkozas`, `tiltott`, `bann_lejar`) VALUES
('argent', '30851b2eb48ecbb49549ec1aaa5d928b', 'Dani', '1988-03-22', 1, 'argent_hun@hotmail.com', 1, 'XD', '', 3, 0, '2010-10-19 22:13:16', 10, 0, '0000-00-00 00:00:00'),
('bandi', 'fe4c1009f80264b02e592207233e48d9', 'Péter András', '1990-09-01', 1, 'bata90@indamail.hu', 0, 'Csak a fradi!!!!', '', 0, 0, '0000-00-00 00:00:00', 10, 0, '0000-00-00 00:00:00'),
('fzoli', '0165c3d06a4a666f042c120076236568', 'Farkas Zoltán', '1989-10-27', 1, 'f.zoli@mailbox.hu', 0, 'Tulajdonos.', '', 3, 1, '2011-04-10 14:44:33', 10, 0, '0000-00-00 00:00:00'),
('fzoltan', '0165c3d06a4a666f042c120076236568', 'Farkas Zoltán', '1989-10-27', 1, 'f.zoli@mailbox.hu', 0, 'Nincs kitöltve.', '', 0, 0, '2010-10-12 22:07:12', 10, 0, '0000-00-00 00:00:00'),
('Norbee', 'c57bd87c12b396731530b3a6716e9e21', 'Ézsiás Norbert', '1988-06-26', 1, 'nnorbee0626@gmail.com', 1, 'Szeva Norbi vagyok!', '', 2, 0, '0000-00-00 00:00:00', 10, 0, '0000-00-00 00:00:00'),
('rajmi', '530ea1472e71035353d32d341ecf6343', 'Aczél Rajmi', '1995-02-13', 1, 'vannic@citromail.hu', 1, 'Nincs kitöltve.', '', 0, 0, '0000-00-00 00:00:00', 10, 0, '0000-00-00 00:00:00'),
('Rencsuu', 'fbf9b3bb56c47713d7b3e3681ece0a63', 'F. Renáta', '1994-09-15', 1, 'renaata@citromail.hu', 1, 'Nincs kitöltve.', '', 1, 0, '0000-00-00 00:00:00', 10, 0, '0000-00-00 00:00:00'),
('rex', '1ac0d72d238c86b2029c8419ba2574a2', 'Rec Ferenc', '1986-06-01', 1, 'refe@freemail.hu', 1, 'Nincs kitöltve.', '', 3, 0, '2010-11-01 20:00:00', 10, 0, '0000-00-00 00:00:00'),
('Steely', 'a04a209df356013988518c0cd3a267c5', 'Imre István', '1985-03-12', 1, 'harcmezo@freemail.hu', 1, 'Requiem: Memento Mori', '', 0, 0, '0000-00-00 00:00:00', 10, 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Tábla szerkezet: `var`
--

CREATE TABLE IF NOT EXISTS `var` (
  `lifetime` bigint(4) unsigned NOT NULL,
  `changetime` bigint(3) unsigned NOT NULL,
  `max_attempt` tinyint(2) unsigned NOT NULL,
  `attempt_bantime` int(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `var`
--

INSERT INTO `var` (`lifetime`, `changetime`, `max_attempt`, `attempt_bantime`) VALUES
(600, 6, 10, 3600);

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `logon`
--
ALTER TABLE `logon`
  ADD CONSTRAINT `logon_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`azon`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`azon`) REFERENCES `user` (`azon`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`jog`) REFERENCES `jog` (`azon`);
