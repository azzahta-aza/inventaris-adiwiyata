-- ===================================================
-- SQL DDL Schema Sistem Inventory (Sesuai ERD)
-- ===================================================

CREATE DATABASE IF NOT EXISTS `inventory`;
USE `inventory`;

-- 1. Tabel Kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Lokasi
CREATE TABLE IF NOT EXISTS `lokasi` (
  `id_lokasi` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lokasi` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(35) NOT NULL,
  `username` VARCHAR(20) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Petugas', 'Kepala') NOT NULL DEFAULT 'Petugas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Barang
CREATE TABLE IF NOT EXISTS `barang` (
  `id_barang` INT AUTO_INCREMENT PRIMARY KEY,
  `id_kategori` INT NOT NULL,
  `id_lokasi` INT NOT NULL,
  `kode_barang` VARCHAR(30) NOT NULL UNIQUE,
  `nama_barang` VARCHAR(100) NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `satuan` VARCHAR(20) NOT NULL,
  `kondisi` ENUM('layak', 'tidak_layak', 'perbaikan') NOT NULL DEFAULT 'layak',
  `keterangan` TEXT,
  CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_barang_lokasi` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi` (`id_lokasi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Peminjaman
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id_pinjam` INT AUTO_INCREMENT PRIMARY KEY,
  `id_barang` INT NOT NULL,
  `nama_peminjam` VARCHAR(100) NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `jumlah` INT NOT NULL,
  `status` ENUM('dipinjam', 'dikembalikan') NOT NULL DEFAULT 'dipinjam',
  `id_user` INT NOT NULL,
  `tanggal_kembali` DATE DEFAULT NULL,
  `kondisi_kembali` VARCHAR(20) DEFAULT NULL,
  CONSTRAINT `fk_peminjaman_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_barang` INT NOT NULL,
  `id_user` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jenis_transaksi` VARCHAR(20) NOT NULL,
  `jumlah` INT NOT NULL,
  `keterangan` VARCHAR(100) DEFAULT NULL,
  CONSTRAINT `fk_transaksi_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data Initial Users (Password: admin123)
INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$10$JAmK6DtgPIgXx5EH0BIC7OeNvr.0diN5w0aea0y9GmdihRHWfL1ra', 'Admin'),
(2, 'Petugas Gudang', 'petugas', '$2y$10$JAmK6DtgPIgXx5EH0BIC7OeNvr.0diN5w0aea0y9GmdihRHWfL1ra', 'Petugas'),
(3, 'Kepala Bagian', 'kepala', '$2y$10$JAmK6DtgPIgXx5EH0BIC7OeNvr.0diN5w0aea0y9GmdihRHWfL1ra', 'Kepala')
ON DUPLICATE KEY UPDATE `id_user`=`id_user`;
