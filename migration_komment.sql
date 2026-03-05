-- Komment tábla hozzáadása meglévő adatbázishoz
-- Futtasd phpMyAdmin-ban vagy mysql parancssorban, ha már van gymlog adatbázisod

USE gymlog;

CREATE TABLE IF NOT EXISTS komment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  posztId INT NOT NULL,
  felhasznaloId INT NOT NULL,
  tartalom VARCHAR(500) NOT NULL,
  datum DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (posztId) REFERENCES poszt(id) ON DELETE CASCADE,
  FOREIGN KEY (felhasznaloId) REFERENCES felhasznalo(id) ON DELETE CASCADE
);
