CREATE DATABASE 'my_budget';

USE my_budget;

CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    amount FLOAT NOT NULL,
    transaction_type VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;


CREATE DATABASE 'my_budget_test';

use my_budget_test;

CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    amount FLOAT NOT NULL,
    transaction_type VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;
