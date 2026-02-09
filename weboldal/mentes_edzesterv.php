<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Hibás kérés.",
    ]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Nincs bejelentkezett felhasználó.",
    ]);
    exit;
}

$raw = file_get_contents("php://input");
$json = json_decode($raw, true);

if (!$json || !isset($json["nev"]) || !isset($json["sorok"]) || !is_array($json["sorok"])) {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Hiányzó vagy hibás adatok.",
    ]);
    exit;
}

$nev   = trim($json["nev"]);
$sorok = $json["sorok"];

if ($nev === "") {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Az edzés neve kötelező.",
    ]);
    exit;
}

$userId = (int)$_SESSION["user_id"];

// Egyszerű tárolás: saját táblába, JSON formában
$createSql = "
    CREATE TABLE IF NOT EXISTS edzesterv_mentes (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        felhasznaloId INT(11) NOT NULL,
        nev VARCHAR(100) NOT NULL,
        tartalom LONGTEXT NOT NULL,
        letrehozva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (!$conn->query($createSql)) {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Adatbázis hiba a tábla létrehozásakor.",
    ]);
    exit;
}

$tartalom = json_encode($sorok, JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare("INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Adatbázis hiba (előkészítés).",
    ]);
    exit;
}

$stmt->bind_param("iss", $userId, $nev, $tartalom);

if ($stmt->execute()) {
    echo json_encode([
        "siker"  => true,
        "uzenet" => "Edzésterv sikeresen elmentve.",
    ]);
} else {
    echo json_encode([
        "siker"  => false,
        "uzenet" => "Nem sikerült elmenteni az edzéstervet.",
    ]);
}

