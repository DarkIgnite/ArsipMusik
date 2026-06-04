-- SQL Database Schema for ArsipMusik
-- Database Name: arsipmusik

CREATE DATABASE IF NOT EXISTS `arsipmusik`;
USE `arsipmusik`;

-- 1. Table structure for tb_user
CREATE TABLE IF NOT EXISTS `tb_user` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Table structure for tb_genre
CREATE TABLE IF NOT EXISTS `tb_genre` (
  `id_genre` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_genre` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Table structure for tb_lagu
CREATE TABLE IF NOT EXISTS `tb_lagu` (
  `id_lagu` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(100) NOT NULL,
  `artis` VARCHAR(100) NOT NULL,
  `album` VARCHAR(100) NOT NULL,
  `id_genre` INT NOT NULL,
  `tahun_rilis` INT(4) NOT NULL,
  `durasi` VARCHAR(10) NOT NULL,
  `gambar` VARCHAR(255) DEFAULT 'default.jpg',
  FOREIGN KEY (`id_genre`) REFERENCES `tb_genre` (`id_genre`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default inserts for tb_user (admin / admin123)
INSERT INTO `tb_user` (`username`, `password`) VALUES 
('admin', '$2y$10$jjipgauedA80NwPRN.s8KOC9MaQXVqj3dsH/ubJa2W9WNbLvo4Wou')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Default inserts for tb_genre
INSERT INTO `tb_genre` (`id_genre`, `nama_genre`) VALUES
(1, 'Pop'),
(2, 'Rock'),
(3, 'R&B'),
(4, 'Hip Hop'),
(5, 'Indie'),
(6, 'Jazz')
ON DUPLICATE KEY UPDATE `id_genre`=VALUES(`id_genre`), `nama_genre`=VALUES(`nama_genre`);
