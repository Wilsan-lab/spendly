-- Spendly Database
-- Clean structure for GitHub
-- No personal user or transaction data included

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('income','expense') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Default categories
-- --------------------------------------------------------

INSERT INTO `categories` (`id`, `name`, `type`) VALUES
(1, 'Salary', 'income'),
(3, 'Other Income', 'income'),
(4, 'Food', 'expense'),
(5, 'Transport', 'expense'),
(6, 'Shopping', 'expense'),
(7, 'Bills', 'expense'),
(8, 'Entertainment', 'expense'),
(9, 'Health', 'expense'),
(10, 'Education', 'expense'),
(11, 'Other Expense', 'expense');

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `theme` varchar(10) NOT NULL DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Indexes for table `categories`
-- --------------------------------------------------------

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------
-- Indexes for table `transactions`
-- --------------------------------------------------------

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

-- --------------------------------------------------------
-- Indexes for table `users`
-- --------------------------------------------------------

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `categories`
-- --------------------------------------------------------

ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `transactions`
-- --------------------------------------------------------

ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------
-- AUTO_INCREMENT for table `users`
-- --------------------------------------------------------

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------
-- Foreign key constraints
-- --------------------------------------------------------

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`);

COMMIT;