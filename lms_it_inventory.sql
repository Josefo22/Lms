-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-03-2025 a las 03:23:46
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lms_it_inventory`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assignmenthistory`
--

CREATE TABLE `assignmenthistory` (
  `assignment_id` int(11) NOT NULL,
  `hardware_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `return_date` timestamp NULL DEFAULT NULL,
  `assigned_by` int(11) NOT NULL,
  `return_received_by` int(11) DEFAULT NULL,
  `assignment_notes` text DEFAULT NULL,
  `return_notes` text DEFAULT NULL,
  `status` enum('Assigned','Returned','Pending Return') DEFAULT 'Assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditdetails`
--

CREATE TABLE `auditdetails` (
  `audit_detail_id` int(11) NOT NULL,
  `audit_id` int(11) NOT NULL,
  `hardware_id` int(11) NOT NULL,
  `expected_status` varchar(50) DEFAULT NULL,
  `actual_status` varchar(50) DEFAULT NULL,
  `expected_location` int(11) DEFAULT NULL,
  `actual_location` int(11) DEFAULT NULL,
  `expected_user` int(11) DEFAULT NULL,
  `actual_user` int(11) DEFAULT NULL,
  `is_discrepancy` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hardware`
--

CREATE TABLE `hardware` (
  `hardware_id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `asset_tag` varchar(50) DEFAULT NULL,
  `model_id` int(11) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry_date` date DEFAULT NULL,
  `status` enum('In Use','In Stock','In Transit','In Repair','Retired','Lost') DEFAULT 'In Stock',
  `condition_status` enum('New','Good','Fair','Poor') DEFAULT 'New',
  `notes` text DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `current_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hardwarecategories`
--

CREATE TABLE `hardwarecategories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventoryaudits`
--

CREATE TABLE `inventoryaudits` (
  `audit_id` int(11) NOT NULL,
  `audit_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `audited_by` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `status` enum('Scheduled','In Progress','Completed','Canceled') DEFAULT 'Scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itstaff`
--

CREATE TABLE `itstaff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('Admin','Support Technician','Inventory Manager','Supervisor') NOT NULL,
  `department` varchar(100) DEFAULT 'IT',
  `responsibilities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `location_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `models`
--

CREATE TABLE `models` (
  `model_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `specifications` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peripheralchangehistory`
--

CREATE TABLE `peripheralchangehistory` (
  `change_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `old_peripheral_id` int(11) DEFAULT NULL,
  `new_peripheral_id` int(11) DEFAULT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `change_reason` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peripherals`
--

CREATE TABLE `peripherals` (
  `peripheral_id` int(11) NOT NULL,
  `hardware_id` int(11) NOT NULL,
  `peripheral_type` enum('Keyboard','Mouse','Monitor','Headset','Webcam','Docking Station','Other') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `shipmentdetails`
--

CREATE TABLE `shipmentdetails` (
  `shipment_detail_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `hardware_id` int(11) NOT NULL,
  `status` enum('Included','Received','Missing','Damaged') DEFAULT 'Included',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` int(11) NOT NULL,
  `shipping_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expected_delivery_date` timestamp NULL DEFAULT NULL,
  `actual_delivery_date` timestamp NULL DEFAULT NULL,
  `origin_location_id` int(11) DEFAULT NULL,
  `destination_location_id` int(11) DEFAULT NULL,
  `recipient_user_id` int(11) DEFAULT NULL,
  `shipping_carrier` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` enum('Prepared','Shipped','In Transit','Delivered','Returned') DEFAULT 'Prepared',
  `created_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `supportrequests`
--

CREATE TABLE `supportrequests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_type` enum('Hardware Issue','Peripheral Request','Replacement','Return','Other') NOT NULL,
  `hardware_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') DEFAULT 'Medium',
  `status` enum('New','Assigned','In Progress','Resolved','Closed','Cancelled') DEFAULT 'New',
  `assigned_to` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `is_remote` tinyint(1) DEFAULT 0,
  `status` enum('Active','Inactive','On Leave') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `assignmenthistory`
--
ALTER TABLE `assignmenthistory`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `hardware_id` (`hardware_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `return_received_by` (`return_received_by`),
  ADD KEY `idx_assignment_status` (`status`);

--
-- Indices de la tabla `auditdetails`
--
ALTER TABLE `auditdetails`
  ADD PRIMARY KEY (`audit_detail_id`),
  ADD KEY `audit_id` (`audit_id`),
  ADD KEY `hardware_id` (`hardware_id`),
  ADD KEY `expected_location` (`expected_location`),
  ADD KEY `actual_location` (`actual_location`),
  ADD KEY `expected_user` (`expected_user`),
  ADD KEY `actual_user` (`actual_user`);

--
-- Indices de la tabla `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indices de la tabla `hardware`
--
ALTER TABLE `hardware`
  ADD PRIMARY KEY (`hardware_id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD UNIQUE KEY `asset_tag` (`asset_tag`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `idx_hardware_status` (`status`),
  ADD KEY `idx_hardware_client` (`client_id`),
  ADD KEY `idx_hardware_location` (`location_id`),
  ADD KEY `idx_hardware_user` (`current_user_id`);

--
-- Indices de la tabla `hardwarecategories`
--
ALTER TABLE `hardwarecategories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indices de la tabla `inventoryaudits`
--
ALTER TABLE `inventoryaudits`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `audited_by` (`audited_by`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `itstaff`
--
ALTER TABLE `itstaff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indices de la tabla `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`model_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `peripheralchangehistory`
--
ALTER TABLE `peripheralchangehistory`
  ADD PRIMARY KEY (`change_id`),
  ADD KEY `fk_pch_request` (`request_id`),
  ADD KEY `fk_pch_old_peripheral` (`old_peripheral_id`),
  ADD KEY `fk_pch_new_peripheral` (`new_peripheral_id`),
  ADD KEY `fk_pch_performed_by` (`performed_by`);

--
-- Indices de la tabla `peripherals`
--
ALTER TABLE `peripherals`
  ADD PRIMARY KEY (`peripheral_id`),
  ADD KEY `hardware_id` (`hardware_id`);

--
-- Indices de la tabla `shipmentdetails`
--
ALTER TABLE `shipmentdetails`
  ADD PRIMARY KEY (`shipment_detail_id`),
  ADD KEY `shipment_id` (`shipment_id`),
  ADD KEY `hardware_id` (`hardware_id`);

--
-- Indices de la tabla `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`),
  ADD KEY `origin_location_id` (`origin_location_id`),
  ADD KEY `destination_location_id` (`destination_location_id`),
  ADD KEY `recipient_user_id` (`recipient_user_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_shipment_status` (`status`);

--
-- Indices de la tabla `supportrequests`
--
ALTER TABLE `supportrequests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hardware_id` (`hardware_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_support_status` (`status`),
  ADD KEY `idx_support_priority` (`priority`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_user_client` (`client_id`),
  ADD KEY `idx_user_location` (`location_id`),
  ADD KEY `idx_user_status` (`status`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `assignmenthistory`
--
ALTER TABLE `assignmenthistory`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditdetails`
--
ALTER TABLE `auditdetails`
  MODIFY `audit_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hardware`
--
ALTER TABLE `hardware`
  MODIFY `hardware_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hardwarecategories`
--
ALTER TABLE `hardwarecategories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventoryaudits`
--
ALTER TABLE `inventoryaudits`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `itstaff`
--
ALTER TABLE `itstaff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `models`
--
ALTER TABLE `models`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `peripheralchangehistory`
--
ALTER TABLE `peripheralchangehistory`
  MODIFY `change_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `peripherals`
--
ALTER TABLE `peripherals`
  MODIFY `peripheral_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `shipmentdetails`
--
ALTER TABLE `shipmentdetails`
  MODIFY `shipment_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `shipments`
--
ALTER TABLE `shipments`
  MODIFY `shipment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `supportrequests`
--
ALTER TABLE `supportrequests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `assignmenthistory`
--
ALTER TABLE `assignmenthistory`
  ADD CONSTRAINT `assignmenthistory_ibfk_1` FOREIGN KEY (`hardware_id`) REFERENCES `hardware` (`hardware_id`),
  ADD CONSTRAINT `assignmenthistory_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignmenthistory_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `assignmenthistory_ibfk_4` FOREIGN KEY (`return_received_by`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `auditdetails`
--
ALTER TABLE `auditdetails`
  ADD CONSTRAINT `auditdetails_ibfk_1` FOREIGN KEY (`audit_id`) REFERENCES `inventoryaudits` (`audit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auditdetails_ibfk_2` FOREIGN KEY (`hardware_id`) REFERENCES `hardware` (`hardware_id`),
  ADD CONSTRAINT `auditdetails_ibfk_3` FOREIGN KEY (`expected_location`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `auditdetails_ibfk_4` FOREIGN KEY (`actual_location`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `auditdetails_ibfk_5` FOREIGN KEY (`expected_user`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `auditdetails_ibfk_6` FOREIGN KEY (`actual_user`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `hardware`
--
ALTER TABLE `hardware`
  ADD CONSTRAINT `hardware_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`),
  ADD CONSTRAINT `hardware_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `hardware_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `hardware_ibfk_4` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `inventoryaudits`
--
ALTER TABLE `inventoryaudits`
  ADD CONSTRAINT `inventoryaudits_ibfk_1` FOREIGN KEY (`audited_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `inventoryaudits_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `inventoryaudits_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);

--
-- Filtros para la tabla `itstaff`
--
ALTER TABLE `itstaff`
  ADD CONSTRAINT `itstaff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`),
  ADD CONSTRAINT `models_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `hardwarecategories` (`category_id`);

--
-- Filtros para la tabla `peripheralchangehistory`
--
ALTER TABLE `peripheralchangehistory`
  ADD CONSTRAINT `fk_pch_new_peripheral` FOREIGN KEY (`new_peripheral_id`) REFERENCES `peripherals` (`peripheral_id`),
  ADD CONSTRAINT `fk_pch_old_peripheral` FOREIGN KEY (`old_peripheral_id`) REFERENCES `peripherals` (`peripheral_id`),
  ADD CONSTRAINT `fk_pch_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_pch_request` FOREIGN KEY (`request_id`) REFERENCES `supportrequests` (`request_id`),
  ADD CONSTRAINT `peripheralchangehistory_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `supportrequests` (`request_id`),
  ADD CONSTRAINT `peripheralchangehistory_ibfk_2` FOREIGN KEY (`old_peripheral_id`) REFERENCES `peripherals` (`peripheral_id`),
  ADD CONSTRAINT `peripheralchangehistory_ibfk_3` FOREIGN KEY (`new_peripheral_id`) REFERENCES `peripherals` (`peripheral_id`),
  ADD CONSTRAINT `peripheralchangehistory_ibfk_4` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `peripherals`
--
ALTER TABLE `peripherals`
  ADD CONSTRAINT `peripherals_ibfk_1` FOREIGN KEY (`hardware_id`) REFERENCES `hardware` (`hardware_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `shipmentdetails`
--
ALTER TABLE `shipmentdetails`
  ADD CONSTRAINT `shipmentdetails_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`shipment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipmentdetails_ibfk_2` FOREIGN KEY (`hardware_id`) REFERENCES `hardware` (`hardware_id`);

--
-- Filtros para la tabla `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`origin_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `shipments_ibfk_2` FOREIGN KEY (`destination_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `shipments_ibfk_3` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `shipments_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `supportrequests`
--
ALTER TABLE `supportrequests`
  ADD CONSTRAINT `supportrequests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `supportrequests_ibfk_2` FOREIGN KEY (`hardware_id`) REFERENCES `hardware` (`hardware_id`),
  ADD CONSTRAINT `supportrequests_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
