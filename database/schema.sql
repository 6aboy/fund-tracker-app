-- Schema only: no personal transactions or account data.
CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` enum('expense','deposit') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date_created` date NOT NULL DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
