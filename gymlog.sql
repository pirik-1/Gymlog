-- Gymlog adatbázis - séma + adatok
-- Bejelentkezés: admin@gymlog.hu / admin123  vagy  felh@gymlog.hu / felh123

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

-- ========== FELHASZNÁLÓK ==========
-- admin@gymlog.hu / admin123  |  felh@gymlog.hu / felh123
INSERT INTO felhasznalo (email, nev, jelszo, admin, magassag, testsuly, nem) VALUES
('admin@gymlog.hu', 'Admin', '$2y$10$zQwQk56lUqFq9PLdabhJnO7gxLfd/oNCUUwCMrez3R2slIcqPKtp2', 1, 180, 85, 'ferfi'),
('felh@gymlog.hu', 'Felhasználó', '$2y$10$v2ePyDgeB0NzKRUc2cC/pepFxNl4HCh8Us29Z/DDhNnsFdGpqtEUa', 0, 175, 72, 'ferfi');

-- Barátság (admin és felh barátok)
INSERT INTO baratsag (kero_id, fogado_id, status) VALUES
(1, 2, 'accepted'),
(2, 1, 'accepted');

-- ========== EDZÉSTERVEK ==========
INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES
(1, 'Felsőtest Push', '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":false},{"rep":10,"suly":65,"kesz":false},{"rep":8,"suly":70,"kesz":false}]},{"nev":"Fej fölé nyomás","szettek":[{"rep":12,"suly":30,"kesz":false},{"rep":10,"suly":35,"kesz":false}]},{"nev":"Tricepsz letolás","szettek":[{"rep":15,"suly":25,"kesz":false},{"rep":12,"suly":30,"kesz":false}]}]'),
(1, 'Láb nap', '[{"nev":"Guggolás","szettek":[{"rep":12,"suly":80,"kesz":false},{"rep":10,"suly":100,"kesz":false},{"rep":8,"suly":120,"kesz":false}]},{"nev":"Lábnyomás","szettek":[{"rep":15,"suly":120,"kesz":false},{"rep":12,"suly":140,"kesz":false}]},{"nev":"Hamstring curl","szettek":[{"rep":12,"suly":40,"kesz":false},{"rep":12,"suly":45,"kesz":false}]}]'),
(1, 'Húzó nap', '[{"nev":"Rudat evezés","szettek":[{"rep":10,"suly":60,"kesz":false},{"rep":10,"suly":70,"kesz":false}]},{"nev":"Húzódzkodás","szettek":[{"rep":8,"suly":0,"kesz":false},{"rep":8,"suly":0,"kesz":false}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":12,"kesz":false},{"rep":10,"suly":14,"kesz":false},{"rep":8,"suly":16,"kesz":false}]}]'),
(1, 'Váll specifikus', '[{"nev":"Oldalemelés","szettek":[{"rep":15,"suly":10,"kesz":false},{"rep":12,"suly":12,"kesz":false}]},{"nev":"Vállból nyomás","szettek":[{"rep":10,"suly":40,"kesz":false},{"rep":10,"suly":45,"kesz":false}]}]'),
(2, 'Kardio + erő', '[{"nev":"Fekvenyomás","szettek":[{"rep":12,"suly":50,"kesz":false},{"rep":10,"suly":55,"kesz":false}]},{"nev":"Guggolás","szettek":[{"rep":15,"suly":60,"kesz":false},{"rep":12,"suly":70,"kesz":false}]}]'),
(2, 'Testrész teljes', '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":false}]},{"nev":"Rudat evezés","szettek":[{"rep":10,"suly":50,"kesz":false}]},{"nev":"Guggolás","szettek":[{"rep":12,"suly":80,"kesz":false}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":10,"kesz":false}]}]');

-- ========== BEFEJEZETT EDZÉSEK ==========
INSERT INTO edzes (nev, idotartam, osszsuly, datum, felhasznaloId, leiras, edzestervMentesId) VALUES
('Felsőtest Push', 1980, 1385, '2025-02-10', 1, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":true},{"rep":10,"suly":65,"kesz":true},{"rep":8,"suly":70,"kesz":true}]},{"nev":"Fej fölé nyomás","szettek":[{"rep":12,"suly":30,"kesz":true},{"rep":10,"suly":35,"kesz":true}]},{"nev":"Tricepsz letolás","szettek":[{"rep":15,"suly":25,"kesz":true},{"rep":12,"suly":30,"kesz":true}]}]', 1),
('Láb nap', 2400, 2100, '2025-02-12', 1, '[{"nev":"Guggolás","szettek":[{"rep":12,"suly":80,"kesz":true},{"rep":10,"suly":100,"kesz":true},{"rep":8,"suly":120,"kesz":true}]},{"nev":"Lábnyomás","szettek":[{"rep":15,"suly":120,"kesz":true},{"rep":12,"suly":140,"kesz":true}]},{"nev":"Hamstring curl","szettek":[{"rep":12,"suly":40,"kesz":true},{"rep":12,"suly":45,"kesz":true}]}]', 2),
('Húzó nap', 2100, 860, '2025-02-14', 1, '[{"nev":"Rudat evezés","szettek":[{"rep":10,"suly":60,"kesz":true},{"rep":10,"suly":70,"kesz":true}]},{"nev":"Húzódzkodás","szettek":[{"rep":8,"suly":0,"kesz":true},{"rep":8,"suly":0,"kesz":true}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":12,"kesz":true},{"rep":10,"suly":14,"kesz":true},{"rep":8,"suly":16,"kesz":true}]}]', 3),
('Felsőtest Push', 1920, 1420, '2025-02-17', 1, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":62,"kesz":true},{"rep":10,"suly":67,"kesz":true},{"rep":8,"suly":72,"kesz":true}]},{"nev":"Fej fölé nyomás","szettek":[{"rep":12,"suly":32,"kesz":true},{"rep":10,"suly":36,"kesz":true}]},{"nev":"Tricepsz letolás","szettek":[{"rep":15,"suly":26,"kesz":true},{"rep":12,"suly":31,"kesz":true}]}]', 1),
('Láb nap', 2550, 2200, '2025-02-19', 1, '[{"nev":"Guggolás","szettek":[{"rep":12,"suly":85,"kesz":true},{"rep":10,"suly":105,"kesz":true},{"rep":8,"suly":125,"kesz":true}]},{"nev":"Lábnyomás","szettek":[{"rep":15,"suly":125,"kesz":true},{"rep":12,"suly":145,"kesz":true}]},{"nev":"Hamstring curl","szettek":[{"rep":12,"suly":42,"kesz":true},{"rep":12,"suly":47,"kesz":true}]}]', 2),
('Felsőtest Push', 1950, 1450, '2025-02-24', 1, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":65,"kesz":true},{"rep":10,"suly":70,"kesz":true},{"rep":8,"suly":75,"kesz":true}]},{"nev":"Fej fölé nyomás","szettek":[{"rep":12,"suly":32,"kesz":true},{"rep":10,"suly":37,"kesz":true}]},{"nev":"Tricepsz letolás","szettek":[{"rep":15,"suly":26,"kesz":true},{"rep":12,"suly":32,"kesz":true}]}]', 1),
('Kardio + erő', 1650, 650, '2025-02-15', 2, '[{"nev":"Fekvenyomás","szettek":[{"rep":12,"suly":50,"kesz":true},{"rep":10,"suly":55,"kesz":true}]},{"nev":"Guggolás","szettek":[{"rep":15,"suly":60,"kesz":true},{"rep":12,"suly":70,"kesz":true}]}]', 5),
('Testrész teljes', 2400, 2100, '2025-02-20', 2, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":60,"kesz":true}]},{"nev":"Rudat evezés","szettek":[{"rep":10,"suly":50,"kesz":true}]},{"nev":"Guggolás","szettek":[{"rep":12,"suly":80,"kesz":true}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":10,"kesz":true}]}]', 6),
('Kardio + erő', 1680, 680, '2025-02-25', 2, '[{"nev":"Fekvenyomás","szettek":[{"rep":12,"suly":52,"kesz":true},{"rep":10,"suly":57,"kesz":true}]},{"nev":"Guggolás","szettek":[{"rep":15,"suly":62,"kesz":true},{"rep":12,"suly":72,"kesz":true}]}]', 5),
('Láb nap', 2200, 1950, '2025-03-01', 2, '[{"nev":"Guggolás","szettek":[{"rep":12,"suly":70,"kesz":true},{"rep":10,"suly":90,"kesz":true}]},{"nev":"Lábnyomás","szettek":[{"rep":15,"suly":100,"kesz":true},{"rep":12,"suly":120,"kesz":true}]}]', 2),
('Felsőtest Push', 2000, 1400, '2025-03-03', 1, '[{"nev":"Fekvenyomás","szettek":[{"rep":10,"suly":65,"kesz":true},{"rep":10,"suly":70,"kesz":true},{"rep":8,"suly":75,"kesz":true}]},{"nev":"Fej fölé nyomás","szettek":[{"rep":12,"suly":32,"kesz":true},{"rep":10,"suly":37,"kesz":true}]},{"nev":"Tricepsz letolás","szettek":[{"rep":15,"suly":27,"kesz":true},{"rep":12,"suly":32,"kesz":true}]}]', 1),
('Húzó nap', 2150, 880, '2025-03-05', 1, '[{"nev":"Rudat evezés","szettek":[{"rep":10,"suly":62,"kesz":true},{"rep":10,"suly":72,"kesz":true}]},{"nev":"Húzódzkodás","szettek":[{"rep":8,"suly":0,"kesz":true},{"rep":9,"suly":0,"kesz":true}]},{"nev":"Bicepsz curl","szettek":[{"rep":12,"suly":13,"kesz":true},{"rep":10,"suly":15,"kesz":true},{"rep":8,"suly":17,"kesz":true}]}]', 3);

-- ========== POSZTOK ==========
INSERT INTO poszt (felhasznaloId, tartalom, edzesId) VALUES
(1, 'Admin befejezett egy edzést: Felsőtest Push (00:33:00)', 1),
(1, 'Admin befejezett egy edzést: Láb nap (00:40:00)', 2),
(1, 'Admin befejezett egy edzést: Húzó nap (00:35:00)', 3),
(1, 'Admin befejezett egy edzést: Felsőtest Push (00:32:00)', 4),
(1, 'Admin befejezett egy edzést: Láb nap (00:42:30)', 5),
(1, 'Admin befejezett egy edzést: Felsőtest Push (00:32:30)', 6),
(2, 'Felhasználó befejezett egy edzést: Kardio + erő (00:27:30)', 7),
(2, 'Felhasználó befejezett egy edzést: Testrész teljes (00:40:00)', 8),
(2, 'Felhasználó befejezett egy edzést: Kardio + erő (00:28:00)', 9),
(2, 'Felhasználó befejezett egy edzést: Láb nap (00:36:40)', 10),
(1, 'Admin befejezett egy edzést: Felsőtest Push (00:33:20)', 11),
(1, 'Admin befejezett egy edzést: Húzó nap (00:35:50)', 12);

-- ========== KOMMENTEK ==========
INSERT INTO komment (posztId, felhasznaloId, tartalom) VALUES
(1, 2, 'Jól halad az admin!'),
(1, 2, 'Push nap mindig jó'),
(2, 2, 'Szuper láb edzés'),
(3, 2, 'Húzó nap a kedvencem'),
(7, 1, 'Szép munka!'),
(7, 1, 'Kardio + erő jó kombináció'),
(8, 1, 'Teljes testrész terv jól néz ki'),
(10, 1, 'Láb nap mindig megéri');
