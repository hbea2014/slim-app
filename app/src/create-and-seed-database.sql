CREATE DATABASE IF NOT EXISTS SlimApp;

USE SlimApp;

CREATE TABLE IF NOT EXISTS Users (
  UserId INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  username VARCHAR(24) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL
)
CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO Users (`username`, `password`, `email`) VALUES 
 ('bob', 'bobpw', 'bob@asdf.df'),
 ('gil', 'gilpw', 'gil@asdf.df'),
 ('leo', 'leopw', 'leo@asdf.df'),
 ('fay', 'faypw', 'fay@asdf.df');

