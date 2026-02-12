<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Jelentkezz be az edzés befejezéséhez."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$nev = trim($json["nev"] ?? "");
$sorok = $json["sorok"] ?? [];
$idotartam = (int)($json["idotartam"] ?? 0);

if (!$nev || !is_array($sorok) || empty($sorok)) {
    echo json_encode(["siker" => false, "uzenet" => "Adj nevet és legalább egy gyakorlatot."]);
    exit;
}

$userId = (int)$_SESSION["user_id"];
$datum = date("Y-m-d");
$osszsuly = 0;
foreach ($sorok as $s) {
    if (isset($s["szettek"]) && is_array($s["szettek"])) {
        foreach ($s["szettek"] as $sz) {
            $rep = (int)($sz["rep"] ?? 0);
            $suly = (int)($sz["suly"] ?? 0);
            $osszsuly += $rep * $suly;
        }
    } else {
        $set = (int)($s["set"] ?? 0);
        $rep = (int)($s["rep"] ?? 0);
        $suly = (int)($s["suly"] ?? 0);
        $osszsuly += $set * $rep * $suly;
    }
}
$leiras = json_encode($sorok, JSON_UNESCAPED_UNICODE);

// Mentés az edzes táblába
$stmt = $conn->prepare("INSERT INTO edzes (nev, idotartam, osszsuly, datum, felhasznaloId, leiras) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siisis", $nev, $idotartam, $osszsuly, $datum, $userId, $leiras);

if (!$stmt->execute()) {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a mentéskor."]);
    exit;
}

$edzesId = (int)$conn->insert_id;

// Poszt tábla létrehozása (ha nincs), vagy edzesId oszlop hozzáadása
$conn->query("CREATE TABLE IF NOT EXISTS poszt (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    felhasznaloId INT(11) NOT NULL,
    tartalom VARCHAR(500) NOT NULL,
    datum DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    edzesId INT(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Poszt létrehozása – név az adatbázisból
$nevStmt = $conn->prepare("SELECT nev FROM felhasznalo WHERE id = ?");
$nevStmt->bind_param("i", $userId);
$nevStmt->execute();
$userNev = ($r = $nevStmt->get_result()->fetch_assoc()) ? $r["nev"] : "Felhasználó";
$idotartamFormazott = gmdate("H:i", $idotartam);
$tartalom = $userNev . " befejezett egy edzést: " . $nev . " (" . $idotartamFormazott . ")";
$pstmt = $conn->prepare("INSERT INTO poszt (felhasznaloId, tartalom, edzesId) VALUES (?, ?, ?)");
$pstmt->bind_param("isi", $userId, $tartalom, $edzesId);
$pstmt->execute();

echo json_encode(["siker" => true, "redirect" => "index.php"]);