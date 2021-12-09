-- phpMyAdmin SQL Dump
-- version 4.9.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 06, 2020 at 10:33 AM
-- Server version: 5.7.26
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ipos_maitama_items`
--

-- --------------------------------------------------------

--
-- Table structure for table `ipos_categories`
--

CREATE TABLE `ipos_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ipos_categories`
--

INSERT INTO `ipos_categories` (`id`, `name`, `department`, `icon`, `created_at`, `updated_at`) VALUES
(79, 'Anaesthetic', 'Pharmacy', NULL, NULL, NULL),
(80, 'Anti Neoplastics & Immunosuppresants', 'Pharmacy', NULL, NULL, NULL),
(81, 'Antiallergy, Antihistamine, Antiasthmatics', 'Pharmacy', NULL, NULL, NULL),
(82, 'Antibiotics', 'Pharmacy', NULL, NULL, NULL),
(83, 'Antidiabetic', 'Pharmacy', NULL, NULL, NULL),
(84, 'Antidotes', 'Pharmacy', NULL, NULL, NULL),
(85, 'Antifungal', 'Pharmacy', NULL, NULL, NULL),
(86, 'Antihelminthic', 'Pharmacy', NULL, NULL, NULL),
(87, 'Anithelminthic', 'Pharmacy', NULL, NULL, NULL),
(88, 'Antihemorrhoids', 'Pharmacy', NULL, NULL, NULL),
(89, 'Antimalaria', 'Pharmacy', NULL, NULL, NULL),
(90, 'Antiseptics, Disinfectants, First Aid', 'Pharmacy', NULL, NULL, NULL),
(91, 'Antithyroid', 'Pharmacy', NULL, NULL, NULL),
(92, 'Antiviral', 'Pharmacy', NULL, NULL, NULL),
(93, 'Aphrodisiacs', 'Pharmacy', NULL, NULL, NULL),
(94, 'Babies', 'Pharmacy', NULL, NULL, NULL),
(95, 'Bone & Muscle Relaxants', 'Pharmacy', NULL, NULL, NULL),
(96, 'Cardiovascular', 'Pharmacy', NULL, NULL, NULL),
(97, 'CNS, Antipsychotic, Antidepressants, Anxiolytics', 'Pharmacy', NULL, NULL, NULL),
(98, 'Contraceptives & Lubricants', 'Pharmacy', NULL, NULL, NULL),
(99, 'Contraceptives, Condoms & Lubricants', 'Pharmacy', NULL, NULL, NULL),
(100, 'Cough, Cold & Flu', 'Pharmacy', NULL, NULL, NULL),
(101, 'Skin & Beauty', 'Suprstore', NULL, NULL, NULL),
(102, 'Skin, Hair & Nails', 'Pharmacy', NULL, NULL, NULL),
(103, 'Dental', 'Pharmacy', NULL, NULL, NULL),
(104, 'Eye & Ear Drops', 'Pharmacy', NULL, NULL, NULL),
(105, 'GIT, Antiulcer, Vomiting, Probiotics', 'Pharmacy', NULL, NULL, NULL),
(106, 'Fertility, Hormones & Steroids', 'Pharmacy', NULL, NULL, NULL),
(107, 'Infusions', 'Pharmacy', NULL, NULL, NULL),
(108, 'First Aid & Medical Devices, Braces & Support', 'Pharmacy', NULL, NULL, NULL),
(109, 'Infusions & Medical Consumables', 'Pharmacy', NULL, NULL, NULL),
(110, 'Insecticides', 'Superstore', NULL, NULL, NULL),
(111, 'Natural/Herbal Supplement', 'Pharmacy', NULL, NULL, NULL),
(112, 'Opiod', 'Pharmacy', NULL, NULL, NULL),
(113, 'Opiods', 'Pharmacy', NULL, NULL, NULL),
(114, 'Opoids', 'Pharmacy', NULL, NULL, NULL),
(115, 'Fever, Pain,  Anti Inflamatory & Antigout', 'Pharmacy', NULL, NULL, NULL),
(116, 'Sleep Aid', 'Pharmacy', NULL, NULL, NULL),
(117, 'Suppository & Passary', 'Pharmacy', NULL, NULL, NULL),
(118, 'Urinary Tract', 'Pharmacy', NULL, NULL, NULL),
(119, 'Urinary Tract ', 'Pharmacy', NULL, NULL, NULL),
(120, 'Vaccines', 'Pharmacy', NULL, NULL, NULL),
(121, 'Vitamins, Blood, Food Supplements', 'Pharmacy', NULL, NULL, NULL),
(122, 'Drinks & Beverages', 'Superstore', NULL, NULL, NULL),
(123, 'Food & Pastries', 'Superstore', NULL, NULL, NULL),
(124, 'Skin & Beauty', 'Superstore', NULL, NULL, NULL),
(125, 'Personal care & Hygiene', 'Superstore', NULL, NULL, NULL),
(126, 'Household', 'Superstore', NULL, NULL, NULL),
(127, 'Tools', 'Superstore', NULL, NULL, NULL),
(128, 'Babies', 'Superstore', NULL, NULL, NULL),
(129, 'Natural/Herbal Supplement', 'Superstore', NULL, NULL, NULL),
(130, 'Creams, Soaps & Shampoos', 'Superstore', NULL, NULL, NULL),
(131, ' Cosmetics', 'Superstore', NULL, NULL, NULL),
(132, 'Drinks & Bevarages', 'Superstore', NULL, NULL, NULL),
(133, 'Sanitory', 'Superstore', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ipos_categories`
--
ALTER TABLE `ipos_categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ipos_categories`
--
ALTER TABLE `ipos_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;
