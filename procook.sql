-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2025 at 09:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `procook`
--

-- --------------------------------------------------------

--
-- Table structure for table `businessprofile`
--

CREATE TABLE `businessprofile` (
  `BusinessID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNumber` varchar(15) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` text NOT NULL,
  `BusinessVerificationStatus` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `BusinessRegistrationDocuments` varchar(255) DEFAULT NULL,
  `RestaurantName` varchar(100) DEFAULT NULL,
  `RestaurantAddress` text DEFAULT NULL,
  `RestaurantContactInfo` varchar(15) DEFAULT NULL,
  `RestaurantDescription` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `BusinessID` int(11) NOT NULL,
  `RiderID` int(11) NOT NULL,
  `TotalPrice` decimal(10,2) NOT NULL,
  `Status` enum('Pending','Confirmed','Waiting for a Courier','Out for Delivery','Delivered','Cancelled','Rejected') DEFAULT 'Pending',
  `DeliveryOption` enum('Fast','Slow') NOT NULL,
  `PaymentMethod` enum('COD','Online') NOT NULL,
  `OrderTimestamp` date DEFAULT current_timestamp(),
  `EstimatedDeliveryTime` time DEFAULT NULL,
  `Distance` decimal(10,2) NOT NULL,
  `DeliveryFee` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `ProfilePicture` varchar(255) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `Age` int(11) NOT NULL,
  `Bio` text DEFAULT NULL,
  `FoodPreferences` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliverypersonnel`
--

CREATE TABLE `deliverypersonnel` (
  `RiderID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNumber` varchar(15) NOT NULL,
  `ProfilePicture` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `LicenseNumber` varchar(50) NOT NULL,
  `IDProof` varchar(255) NOT NULL,
  `DeliveryVerificationStatus` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `FavoriteID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `BusinessID` int(11) DEFAULT NULL,
  `MenuID` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `MenuID` int(11) NOT NULL,
  `BusinessID` int(11) NOT NULL,
  `FoodName` varchar(100) NOT NULL,
  `FoodImage` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Description` text DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Availability` enum('Available','Not Available') DEFAULT 'Available',
  `Stocks` int(11) DEFAULT 0,
  `PreparationTime` int(11) DEFAULT NULL,
  `SpecialTags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `businessprofile`
--
ALTER TABLE `businessprofile`
  ADD PRIMARY KEY (`BusinessID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Password` (`Password`);

--
-- Indexes for table `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`,`BusinessID`),
  ADD KEY `RiderID` (`RiderID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Password` (`Password`);

--
-- Indexes for table `deliverypersonnel`
--
ALTER TABLE `deliverypersonnel`
  ADD PRIMARY KEY (`RiderID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`FavoriteID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `BusinessID` (`BusinessID`),
  ADD KEY `MenuID` (`MenuID`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`MenuID`),
  ADD KEY `BusinessID` (`BusinessID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `businessprofile`
--
ALTER TABLE `businessprofile`
  MODIFY `BusinessID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `checkout`
--
ALTER TABLE `checkout`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deliverypersonnel`
--
ALTER TABLE `deliverypersonnel`
  MODIFY `RiderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `FavoriteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `MenuID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`BusinessID`) REFERENCES `businessprofile` (`BusinessID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_3` FOREIGN KEY (`MenuID`) REFERENCES `menu` (`MenuID`) ON DELETE CASCADE;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`BusinessID`) REFERENCES `businessprofile` (`BusinessID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
