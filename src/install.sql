
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `bulk_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `responseID` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `tID` int(4) NOT NULL,
  `resultTitle` varchar(255) NOT NULL,
  `errorTitle` varchar(255) NOT NULL,
  `errorDescription` mediumtext NOT NULL,
  `standards` mediumtext NOT NULL,
  `errorSnippet` varchar(255) NOT NULL,
  `issueLine` int(6) NOT NULL,
  `issueColumn` int(6) NOT NULL,
  `xpath` mediumtext NOT NULL,
  `certainty` int(3) NOT NULL,
  `priority` int(3) NOT NULL,
  `ref` varchar(255) NOT NULL,
  `signature` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
`queueID` int(6) NOT NULL,
  `url` varchar(255) NOT NULL,
  `dateAdded` datetime NOT NULL,
  `status` int(3) NOT NULL,
  `errors` int(4) NOT NULL DEFAULT '0',
  `warnings` int(4) NOT NULL DEFAULT '0',
  `tested` enum('0','1') NOT NULL DEFAULT '0',
  `retries` int(2) NOT NULL DEFAULT '0',
  `dateTested` datetime NOT NULL,
  `rawResponse` mediumtext NOT NULL,
  `responseID` varchar(255) NOT NULL,
  `testing` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `responseLog`
--

CREATE TABLE `responseLog` (
  `responseID` varchar(255) NOT NULL,
  `dateAdded` datetime NOT NULL,
  `url` varchar(255) NOT NULL,
  `status` int(3) NOT NULL,
  `errors` int(4) NOT NULL,
  `warnings` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
 ADD KEY `responseID` (`responseID`), ADD KEY `tID` (`tID`), ADD KEY `certainty` (`certainty`), ADD KEY `priority` (`priority`), ADD KEY `signature` (`signature`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
 ADD PRIMARY KEY (`queueID`), ADD KEY `retries` (`retries`), ADD KEY `testing` (`testing`), ADD KEY `tested` (`tested`);

--
-- Indexes for table `responseLog`
--
ALTER TABLE `responseLog`
 ADD KEY `responseID` (`responseID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
MODIFY `queueID` int(6) NOT NULL AUTO_INCREMENT;