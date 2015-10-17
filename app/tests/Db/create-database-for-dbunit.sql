CREATE DATABASE IF NOT EXISTS SlimApp_tests;

USE SlimApp_tests;

CREATE TABLE IF NOT EXISTS Users (
  UserId INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  username VARCHAR(24) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL
)
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

