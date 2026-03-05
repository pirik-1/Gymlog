-- Gymlog adatbázis - frissített séma + dummy adatok
-- Használat: Hozd létre a gymlog adatbázist, majd importáld ezt a fájlt phpMyAdmin-ban

SET NAMES utf8mb4;

-- Régi adatok törlése
DROP TABLE IF EXISTS komment;
DROP TABLE IF EXISTS poszt;
DROP TABLE IF EXISTS edzes;
DROP TABLE IF EXISTS edzesterv_mentes;
DROP TABLE IF EXISTS baratsag;
DROP TABLE IF EXISTS gyakorlat_ajanlas;
DROP TABLE IF EXISTS adatok;
DROP TABLE IF EXISTS sorozat;
DROP TABLE IF EXISTS edzestervgyakorlat;
DROP TABLE IF EXISTS edzesterv;
DROP TABLE IF EXISTS gyakorlathozzaad;
DROP TABLE IF EXISTS gyakorlat;
DROP TABLE IF EXISTS felhasznalo;

-- Felhasználók
CREATE TABLE felhasznalo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL UNIQUE,
  nev VARCHAR(50) NOT NULL,
  jelszo VARCHAR(255) NOT NULL,
  admin TINYINT(1) DEFAULT 0,
  magassag INT DEFAULT NULL,
  testsuly INT DEFAULT NULL,
  nem VARCHAR(20) DEFAULT NULL
);

-- Edzések
CREATE TABLE edzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nev VARCHAR(30) NOT NULL,
  idotartam INT DEFAULT NULL,
  osszsuly INT DEFAULT NULL,
  datum DATE NOT NULL,
  felhasznaloId INT NOT NULL,
  leiras TEXT,
  edzestervMentesId INT DEFAULT NULL,
  FOREIGN KEY (felhasznaloId) REFERENCES felhasznalo(id) ON DELETE CASCADE
);

-- Posztok (hírfolyam)
CREATE TABLE poszt (
  id INT AUTO_INCREMENT PRIMARY KEY,
  felhasznaloId INT NOT NULL,
  tartalom VARCHAR(500) NOT NULL,
  datum DATETIME DEFAULT CURRENT_TIMESTAMP,
  edzesId INT DEFAULT NULL,
  FOREIGN KEY (felhasznaloId) REFERENCES felhasznalo(id) ON DELETE CASCADE,
  FOREIGN KEY (edzesId) REFERENCES edzes(id) ON DELETE SET NULL
);

-- Kommentek a posztokhoz
CREATE TABLE komment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  posztId INT NOT NULL,
  felhasznaloId INT NOT NULL,
  tartalom VARCHAR(500) NOT NULL,
  datum DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (posztId) REFERENCES poszt(id) ON DELETE CASCADE,
  FOREIGN KEY (felhasznaloId) REFERENCES felhasznalo(id) ON DELETE CASCADE
);

-- Mentett edzéstervek
CREATE TABLE edzesterv_mentes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  felhasznaloId INT NOT NULL,
  nev VARCHAR(100) NOT NULL,
  tartalom LONGTEXT NOT NULL,
  letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (felhasznaloId) REFERENCES felhasznalo(id) ON DELETE CASCADE
);

-- Barátság
CREATE TABLE baratsag (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kero_id INT NOT NULL,
  fogado_id INT NOT NULL,
  status ENUM('pending','accepted') DEFAULT 'pending',
  datum DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_keres (kero_id, fogado_id),
  FOREIGN KEY (kero_id) REFERENCES felhasznalo(id) ON DELETE CASCADE,
  FOREIGN KEY (fogado_id) REFERENCES felhasznalo(id) ON DELETE CASCADE
);

-- Gyakorlat javaslat (admin jóváhagyja)
CREATE TABLE gyakorlat_ajanlas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  felhasznalo_id INT NOT NULL,
  nev VARCHAR(100) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  datum DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (felhasznalo_id) REFERENCES felhasznalo(id) ON DELETE CASCADE
);

-- ========== DUMMY ADATOK ==========
-- admin / admin123 (rendszergazda), felh / felh123 (simafelhasznalo), tobbi: password
INSERT INTO felhasznalo (email, nev, jelszo, admin, magassag, testsuly, nem) VALUES
('admin@gymlog.hu', 'Admin', '$2y$10$zQwQk56lUqFq9PLdabhJnO7gxLfd/oNCUUwCMrez3R2slIcqPKtp2', 1, NULL, NULL, NULL),
('felh@gymlog.hu', 'Felhasználó', '$2y$10$v2ePyDgeB0NzKRUc2cC/pepFxNl4HCh8Us29Z/DDhNnsFdGpqtEUa', 0, NULL, NULL, NULL),
('anna@pelda.hu', 'Anna Kiss', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 165, 58, 'no'),
('bela@pelda.hu', 'Béla Nagy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 182, 78, 'ferfi'),
('cili@pelda.hu', 'Cili Tóth', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 170, 65, 'no');

-- Edzéstervek (Anna id=3, Béla id=4)
INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES
(3, 'Felsőtest', '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":false},{"rep":10,"suly":60,"kesz":false}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":10,"kesz":false}]}]'),
(3, 'Láb nap', '[{"nev":"Guggolás","szettek":[{"rep":15,"suly":50,"kesz":false}]}]'),
(4, 'Pull nap', '[{"nev":"Húzódzkodás","szettek":[{"rep":8,"suly":0,"kesz":false},{"rep":8,"suly":0,"kesz":false}]}]');

-- Edzések (befejezett)
INSERT INTO edzes (nev, idotartam, osszsuly, datum, felhasznaloId, leiras, edzestervMentesId) VALUES
('Felsőtest', 1850, 1200, '2025-02-28', 3, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":true},{"rep":10,"suly":60,"kesz":true}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":10,"kesz":true}]}]', 1),
('Láb nap', 2100, 750, '2025-03-02', 3, '[{"nev":"Guggolás","szettek":[{"rep":15,"suly":50,"kesz":true},{"rep":15,"suly":50,"kesz":true}]}]', 2),
('Pull nap', 2400, 0, '2025-03-04', 4, '[{"nev":"Húzódzkodás","szettek":[{"rep":8,"suly":0,"kesz":true},{"rep":8,"suly":0,"kesz":true}]}]', 3),
('Felsőtest', 900, 600, '2025-03-05', 4, '[{"nev":"Fekvenyomás","szettek":[{"rep":8,"suly":60,"kesz":true}]}]', NULL);

-- Posztok
INSERT INTO poszt (felhasznaloId, tartalom, edzesId) VALUES
(3, 'Anna Kiss befejezett egy edzést: Felsőtest (00:30:50)', 1),
(3, 'Anna Kiss befejezett egy edzést: Láb nap (00:35:00)', 2),
(4, 'Béla Nagy befejezett egy edzést: Pull nap (00:40:00)', 3);

-- Kommentek (példa)
INSERT INTO komment (posztId, felhasznaloId, tartalom) VALUES
(1, 4, 'Szuper edzés!'),
(1, 5, 'Gratulálok!'),
(2, 4, 'Láb nap a legjobb');

-- Barátság (Anna-Béla barátok, Cili kérte Bélát)
INSERT INTO baratsag (kero_id, fogado_id, status) VALUES
(3, 4, 'accepted'),
(4, 3, 'accepted'),
(5, 4, 'pending');
