SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `Meeting` (
  `ID` int(11) NOT NULL,
  `PublicID` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Name` varchar(255) NOT NULL DEFAULT '',
  `Time` datetime NOT NULL,
  `FinalPlace` int(11) DEFAULT NULL,
  `IsCancelled` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Meeting_Participant` (
  `Meeting` int(11) NOT NULL,
  `User` int(11) NOT NULL,
  `WillAttend` bit(1) NOT NULL DEFAULT b'1',
  `IsOwner` bit(1) NOT NULL DEFAULT b'0',
  `Latitude` decimal(16,14) NOT NULL,
  `Longitude` decimal(17,14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Meeting_SuggestedPlace` (
  `Meeting` int(11) NOT NULL,
  `Place` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Meeting_VoteForPlace` (
  `Meeting` int(11) NOT NULL,
  `User` int(11) NOT NULL,
  `Place` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Place` (
  `ID` int(11) NOT NULL,
  `GoogleMapsPlaceID` varchar(320) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Address` varchar(200) NOT NULL,
  `Latitude` decimal(16,14) NOT NULL,
  `Longitude` decimal(17,14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `User` (
  `ID` int(11) NOT NULL,
  `Username` varchar(32) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `Meeting`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `PublicID` (`PublicID`),
  ADD KEY `FinalPlace` (`FinalPlace`);

ALTER TABLE `Meeting_Participant`
  ADD PRIMARY KEY (`Meeting`,`User`),
  ADD KEY `Meeting` (`Meeting`),
  ADD KEY `User` (`User`);

ALTER TABLE `Meeting_SuggestedPlace`
  ADD PRIMARY KEY (`Meeting`,`Place`),
  ADD KEY `Meeting` (`Meeting`),
  ADD KEY `PlaceID` (`Place`);

ALTER TABLE `Meeting_VoteForPlace`
  ADD PRIMARY KEY (`Meeting`,`User`,`Place`),
  ADD KEY `Meeting_VoteForPlace_ibfk_2` (`User`),
  ADD KEY `Meeting_VoteForPlace_ibfk_3` (`Place`);

ALTER TABLE `Place`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `GoogleMapsPlaceID` (`GoogleMapsPlaceID`);

ALTER TABLE `User`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `username` (`Username`),
  ADD UNIQUE KEY `email` (`Email`);


ALTER TABLE `Meeting`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

ALTER TABLE `Place`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

ALTER TABLE `User`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;


ALTER TABLE `Meeting`
  ADD CONSTRAINT `Meeting_ibfk_1` FOREIGN KEY (`FinalPlace`) REFERENCES `Place` (`ID`) ON UPDATE CASCADE;

ALTER TABLE `Meeting_Participant`
  ADD CONSTRAINT `Meeting_Participant_ibfk_2` FOREIGN KEY (`User`) REFERENCES `User` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Meeting_Participant_ibfk_3` FOREIGN KEY (`Meeting`) REFERENCES `Meeting` (`ID`) ON UPDATE CASCADE;

ALTER TABLE `Meeting_SuggestedPlace`
  ADD CONSTRAINT `Meeting_SuggestedPlace_ibfk_2` FOREIGN KEY (`Meeting`) REFERENCES `Meeting` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Meeting_SuggestedPlace_ibfk_3` FOREIGN KEY (`Place`) REFERENCES `Place` (`ID`) ON UPDATE CASCADE;

ALTER TABLE `Meeting_VoteForPlace`
  ADD CONSTRAINT `Meeting_VoteForPlace_ibfk_1` FOREIGN KEY (`Meeting`) REFERENCES `Meeting_SuggestedPlace` (`Meeting`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Meeting_VoteForPlace_ibfk_2` FOREIGN KEY (`User`) REFERENCES `User` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Meeting_VoteForPlace_ibfk_3` FOREIGN KEY (`Place`) REFERENCES `Meeting_SuggestedPlace` (`Place`) ON UPDATE CASCADE;
COMMIT;
