CREATE DATABASE IF NOT EXISTS db_cafe
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_cafe;

CREATE TABLE IF NOT EXISTS users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin', 'superadmin') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu (
  id_menu INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_menu VARCHAR(120) NOT NULL,
  kategori ENUM('Coffee', 'Non-Coffee', 'Snack', 'Main Course') NOT NULL,
  harga INT UNSIGNED NOT NULL DEFAULT 0,
  stok INT NOT NULL DEFAULT 0,
  deskripsi TEXT NULL,
  foto VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  id_event INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul_event VARCHAR(150) NOT NULL,
  tanggal_event DATE NOT NULL,
  deskripsi_event TEXT NULL,
  status_event VARCHAR(50) NOT NULL DEFAULT 'active',
  foto_cover VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery (
  id_gallery INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_event INT UNSIGNED NULL,
  judul VARCHAR(150) NULL,
  deskripsi TEXT NULL,
  file_foto VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gallery_event
    FOREIGN KEY (id_event) REFERENCES events(id_event)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedback (
  id_feedback INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_pelanggan VARCHAR(100) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  komentar TEXT NOT NULL,
  status_moderasi ENUM('pending', 'tampil') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_feedback_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS human_archive (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  role VARCHAR(100) NOT NULL,
  quote TEXT NULL,
  image VARCHAR(255) NOT NULL,
  display_order INT NOT NULL DEFAULT 1,
  status ENUM('active', 'hidden') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS penjualan (
  id_penjualan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total INT UNSIGNED NOT NULL DEFAULT 0,
  metode_pembayaran ENUM('CASH', 'QRIS') NOT NULL DEFAULT 'CASH',
  uang_diterima INT UNSIGNED NOT NULL DEFAULT 0,
  kembalian INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detail_penjualan (
  id_detail INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_penjualan INT UNSIGNED NOT NULL,
  id_menu INT UNSIGNED NULL,
  nama_menu VARCHAR(120) NOT NULL,
  harga INT UNSIGNED NOT NULL DEFAULT 0,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  subtotal INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_detail_penjualan
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_detail_menu
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password, nama_lengkap, role)
VALUES
  ('admin', MD5('admin123'), 'Administrator Woelandari', 'superadmin'),
  ('kasir', MD5('kasir123'), 'Kasir Woelandari', 'admin')
ON DUPLICATE KEY UPDATE
  nama_lengkap = VALUES(nama_lengkap),
  role = VALUES(role);

INSERT INTO menu (nama_menu, kategori, harga, stok, deskripsi, foto)
VALUES
  ('Espresso', 'Coffee', 18000, 25, 'Shot kopi pekat dengan karakter rasa tegas.', '699ee3bc8b104.jpg'),
  ('Cafe Latte', 'Coffee', 28000, 20, 'Espresso dengan susu steamed yang lembut.', '699ef9b13ab4f.jpg'),
  ('Manual Brew', 'Coffee', 32000, 15, 'Seduhan manual dari biji kopi pilihan.', 'menu_1772017150_543.jpg'),
  ('Chocolate Ice', 'Non-Coffee', 26000, 18, 'Minuman cokelat dingin yang creamy.', 'menu_69cd43e48b44a.jpg'),
  ('French Fries', 'Snack', 22000, 30, 'Kentang goreng renyah untuk teman ngopi.', 'menu_69cd443227012.jpg'),
  ('Rice Bowl Chicken', 'Main Course', 35000, 12, 'Nasi dengan ayam berbumbu dan saus khas.', 'menu_69e826a6daef2.jpg')
ON DUPLICATE KEY UPDATE
  nama_menu = VALUES(nama_menu);

INSERT INTO events (judul_event, tanggal_event, deskripsi_event, status_event, foto_cover)
VALUES
  ('Coffee Sharing Session', '2026-05-15', 'Sesi berbagi cerita tentang proses seduh dan rasa kopi lokal.', 'active', 'event_69ceb10f1964c.jpg')
ON DUPLICATE KEY UPDATE
  judul_event = VALUES(judul_event);

INSERT INTO gallery (id_event, judul, deskripsi, file_foto)
SELECT id_event, 'Behind The Bar', 'Dokumentasi suasana bar dan proses penyeduhan.', 'gallery_69ceb12c7908d.jpg'
FROM events
WHERE judul_event = 'Coffee Sharing Session'
LIMIT 1;

INSERT INTO human_archive (name, role, quote, image, display_order, status)
VALUES
  ('Woelandari Team', 'COFFEE CREW', 'Merawat rasa dari hulu sampai hilir.', 'human_1776619942.jpeg', 1, 'active');

INSERT INTO feedback (nama_pelanggan, rating, komentar, status_moderasi)
VALUES
  ('Pelanggan Pertama', 5, 'Kopinya enak dan suasananya nyaman.', 'tampil');
