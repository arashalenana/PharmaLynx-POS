-- PharmaLynx POS Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+03:00";

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `medicines`;
DROP TABLE IF EXISTS `users`;

-- Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') NOT NULL DEFAULT 'Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Medicines Table
CREATE TABLE `medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `buying_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `expiry_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers Table
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL, 
  `last_name` varchar(50) NOT NULL, 
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Prescriptions Table
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales Table
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sale Items Table
CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Users
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin123', 'Admin'),
(2, 'staff', 'staff123', 'Staff');

-- Seed Customers 
INSERT INTO `customers` (`id`, `first_name`, `last_name`, `phone`) VALUES
(1, 'John', 'Kamau', '0712345678'),
(2, 'Mary', 'Wanjiku', '0722334455'),
(3, 'David', 'Ochieng', '0733445566'),
(4, 'Sarah', 'Achieng', '0744556677'),
(5, 'Michael', 'Njoroge', '0755667788'),
(6, 'Emily', 'Muthoni', '0766778899'),
(7, 'James', 'Kipkorir', '0777889900'),
(8, 'Jessica', 'Nyambura', '0788990011'),
(9, 'Robert', 'Mutua', '0799001122'),
(10, 'Linda', 'Khadija', '0700112233'),
(11, 'William', 'Ouma', '0711223344'),
(12, 'Elizabeth', 'Moraa', '0722334455');

-- Seed Medicines
INSERT INTO `medicines` (`name`, `category`, `batch_no`, `quantity`, `buying_price`, `selling_price`, `expiry_date`) VALUES
('Paracetamol 500mg', 'Analgesics', 'B-101', 150, 2.00, 5.00, '2027-12-31'),
('Amoxicillin 250mg', 'Antibiotics', 'B-102', 8, 15.00, 25.00, '2026-06-15'),
('Ibuprofen 400mg', 'Analgesics', 'B-103', 200, 3.50, 8.00, '2027-05-20'),
('Cetirizine 10mg', 'Antihistamines', 'B-104', 120, 5.00, 12.00, '2026-05-25'),
('Metformin 500mg', 'Antidiabetics', 'B-105', 300, 10.00, 20.00, '2028-01-10'),
('Amlodipine 5mg', 'Antihypertensives', 'B-106', 5, 12.00, 22.00, '2026-05-10'),
('Omeprazole 20mg', 'Antacids', 'B-107', 180, 8.00, 18.00, '2027-09-30'),
('Azithromycin 500mg', 'Antibiotics', 'B-108', 45, 50.00, 85.00, '2026-08-12'),
('Salbutamol Inhaler', 'Respiratory', 'B-109', 30, 150.00, 250.00, '2027-03-15'),
('Loratadine 10mg', 'Antihistamines', 'B-110', 90, 6.00, 15.00, '2026-05-05'),
('Ciprofloxacin 500mg', 'Antibiotics', 'B-111', 60, 25.00, 45.00, '2027-11-20'),
('Diclofenac 50 pill', 'Analgesics', 'B-112', 250, 4.00, 10.00, '2028-02-28'),
('Losartan 50mg', 'Antihypertensives', 'B-113', 140, 15.00, 30.00, '2027-07-15'),
('Atorvastatin 20mg', 'Statins', 'B-114', 100, 20.00, 45.00, '2027-10-05'),
('Hydrochlorothiazide', 'Diuretics', 'B-115', 85, 5.00, 12.00, '2026-12-10'),
('Prednisone 5mg', 'Steroids', 'B-116', 110, 3.00, 8.00, '2026-09-20'),
('Gabapentin 300mg', 'Anticonvulsants', 'B-117', 70, 35.00, 65.00, '2027-04-18'),
('Sertraline 50mg', 'Antidepressants', 'B-118', 50, 40.00, 75.00, '2027-06-30'),
('Furosemide 40mg', 'Diuretics', 'B-119', 130, 4.00, 10.00, '2026-11-15'),
('Pantoprazole 40mg', 'Antacids', 'B-120', 95, 12.00, 25.00, '2027-08-22'),
('Meloxicam 15mg', 'Analgesics', 'B-121', 160, 10.00, 22.00, '2027-12-05'),
('Clopidogrel 75mg', 'Antiplatelets', 'B-122', 40, 30.00, 55.00, '2026-10-10'),
('Levothyroxine 50mcg', 'Hormones', 'B-123', 220, 15.00, 35.00, '2028-05-15'),
('Montelukast 10mg', 'Respiratory', 'B-124', 75, 25.00, 50.00, '2027-02-20'),
('Rosuvastatin 10mg', 'Statins', 'B-125', 115, 25.00, 55.00, '2027-09-10'),
('Escitalopram 10mg', 'Antidepressants', 'B-126', 65, 35.00, 70.00, '2027-03-25'),
('Alprazolam 0.5mg', 'Anxiolytics', 'B-127', 30, 20.00, 45.00, '2026-07-15'),
('Warfarin 5mg', 'Anticoagulants', 'B-128', 55, 10.00, 25.00, '2026-12-30'),
('Spironolactone 25mg', 'Diuretics', 'B-129', 80, 12.00, 28.00, '2027-01-15'),
('Metoprolol 50mg', 'Antihypertensives', 'B-130', 125, 18.00, 35.00, '2027-05-10'),
('Tramadol 50mg', 'Analgesics', 'B-131', 45, 15.00, 35.00, '2026-08-05'),
('Fluoxetine 20mg', 'Antidepressants', 'B-132', 90, 25.00, 50.00, '2027-11-12'),
('Citalopram 20mg', 'Antidepressants', 'B-133', 70, 22.00, 45.00, '2027-04-30'),
('Doxycycline 100mg', 'Antibiotics', 'B-134', 110, 12.00, 25.00, '2026-09-15'),
('Tamsulosin 0.4mg', 'Urologicals', 'B-135', 50, 45.00, 85.00, '2027-10-20'),
('Carvedilol 6.25mg', 'Antihypertensives', 'B-136', 95, 15.00, 32.00, '2027-06-10'),
('Clonazepam 0.5mg', 'Anxiolytics', 'B-137', 40, 18.00, 40.00, '2026-11-25'),
('Lisinopril 10mg', 'Antihypertensives', 'B-138', 150, 10.00, 22.00, '2027-12-15'),
('Simvastatin 20mg', 'Statins', 'B-139', 130, 12.00, 28.00, '2027-08-05'),
('Lorazepam 1mg', 'Anxiolytics', 'B-140', 35, 22.00, 50.00, '2026-10-20'),
('Cyclobenzaprine', 'Muscle Relaxants', 'B-141', 60, 15.00, 35.00, '2027-03-10'),
('Allopurinol 100mg', 'Antigout', 'B-142', 105, 8.00, 20.00, '2027-07-25'),
('Venlafaxine 75mg', 'Antidepressants', 'B-143', 55, 45.00, 90.00, '2027-05-15'),
('Bupropion 150mg', 'Antidepressants', 'B-144', 45, 50.00, 95.00, '2027-09-30'),
('Ranitidine 150mg', 'Antacids', 'B-145', 140, 5.00, 12.00, '2026-12-15'),
('Naproxen 500mg', 'Analgesics', 'B-146', 120, 12.00, 28.00, '2027-11-05'),
('Duloxetine 30mg', 'Antidepressants', 'B-147', 65, 55.00, 110.00, '2027-04-20'),
('Aspirin 81mg', 'Antiplatelets', 'B-148', 300, 2.00, 5.00, '2028-06-30'),
('Potassium Chloride', 'Supplements', 'B-149', 80, 15.00, 35.00, '2027-02-15'),
('Baclofen 10mg', 'Muscle Relaxants', 'B-150', 75, 20.00, 45.00, '2027-08-10'),
('Methocarbamol', 'Muscle Relaxants', 'B-151', 90, 18.00, 40.00, '2027-10-15'),
('Celecoxib 200mg', 'Analgesics', 'B-152', 50, 60.00, 120.00, '2027-06-25'),
('Mirtazapine 15mg', 'Antidepressants', 'B-153', 40, 40.00, 85.00, '2027-03-15'),
('Divalproex 500mg', 'Anticonvulsants', 'B-154', 35, 55.00, 110.00, '2027-09-10'),
('Donepezil 5mg', 'Anti-Alzheimer', 'B-155', 25, 70.00, 140.00, '2027-05-20'),
('Pravastatin 20mg', 'Statins', 'B-156', 85, 15.00, 35.00, '2027-11-30'),
('Trazodone 50mg', 'Antidepressants', 'B-157', 110, 12.00, 28.00, '2027-07-15'),
('Buspirone 10mg', 'Anxiolytics', 'B-158', 60, 25.00, 55.00, '2027-04-10'),
('Finasteride 5mg', 'Urologicals', 'B-159', 45, 40.00, 85.00, '2027-10-05'),
('Oxybutynin 5mg', 'Urologicals', 'B-160', 70, 18.00, 42.00, '2027-08-20'),
('Glipizide 5mg', 'Antidiabetics', 'B-161', 140, 8.00, 18.00, '2027-12-10'),
('Pioglitazone 15mg', 'Antidiabetics', 'B-162', 55, 35.00, 75.00, '2027-06-15'),
('Gliclazide 80mg', 'Antidiabetics', 'B-163', 120, 10.00, 22.00, '2027-09-25'),
('Vildagliptin 50mg', 'Antidiabetics', 'B-164', 80, 50.00, 95.00, '2027-03-30'),
('Sitagliptin 100mg', 'Antidiabetics', 'B-165', 60, 80.00, 150.00, '2027-11-15');

-- Seed Prescriptions
INSERT INTO `prescriptions` (`customer_id`, `notes`, `date`) VALUES
(1, 'Take Paracetamol 500mg twice daily for 3 days.', '2026-04-28'),
(2, 'Amoxicillin 250mg: 1 tablet every 8 hours for 7 days.', '2026-04-29'),
(3, 'Metformin 500mg: 1 tablet with breakfast and dinner.', '2026-04-30'),
(4, 'Cetirizine 10mg: 1 tablet at night for allergy relief.', '2026-05-01'),
(5, 'Amlodipine 5mg: 1 tablet daily in the morning.', '2026-05-01'),
(6, 'Salbutamol Inhaler: 2 puffs as needed for shortness of breath.', '2026-04-25'),
(7, 'Omeprazole 20mg: 1 capsule 30 minutes before breakfast.', '2026-04-26'),
(8, 'Atorvastatin 20mg: 1 tablet daily at bedtime.', '2026-04-27'),
(9, 'Gabapentin 300mg: 1 capsule three times daily.', '2026-04-28'),
(10, 'Levothyroxine 50mcg: 1 tablet daily on an empty stomach.', '2026-04-29'),
(11, 'Warfarin 5mg: 1 tablet daily as directed by doctor.', '2026-04-30'),
(12, 'Montelukast 10mg: 1 tablet daily in the evening.', '2026-05-01');

COMMIT;
