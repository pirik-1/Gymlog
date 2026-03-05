<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

$adatok = json_decode(file_get_contents("php://input"), true);
$nev = trim($adatok["nev"] ?? "");
$sorok = $adatok["sorok"] ?? [];
$idotartam = (int)($adatok["idotartam"] ?? 0);
$tervId = isset($adatok["terv_id"]) && is_numeric($adatok["terv_id"]) ? (int)$adatok["terv_id"] : null;

if (!$nev || !is_array($sorok) || empty($sorok)) {
    echo json_encode(["siker" => false, "uzenet" => "Adj nevet és legalább egy gyakorlatot."]);
    exit;
}

$felhasznaloId = (int)$_SESSION["user_id"];
$datum = date("Y-m-d");

// Összsúly számolás
$osszsuly = 0;
foreach ($sorok as $sor) {
    if (isset($sor["szettek"]) && is_array($sor["szettek"])) {
        foreach ($sor["szettek"] as $sz) {
            $osszsuly += (int)($sz["rep"] ?? 0) * (int)($sz["suly"] ?? 0);
        }
    } else {
        $osszsuly += (int)($sor["set"] ?? 0) * (int)($sor["rep"] ?? 0) * (int)($sor["suly"] ?? 0);
    }
}

$leiras = json_encode($sorok, JSON_UNESCAPED_UNICODE);

// Edzés mentése
if ($tervId) {
    $mentes = $conn->prepare("INSERT INTO edzes (nev, idotartam, osszsuly, datum, felhasznaloId, leiras, edzestervMentesId) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $mentes->bind_param("siisisi", $nev, $idotartam, $osszsuly, $datum, $felhasznaloId, $leiras, $tervId);
} else {
    $mentes = $conn->prepare("INSERT INTO edzes (nev, idotartam, osszsuly, datum, felhasznaloId, leiras) VALUES (?, ?, ?, ?, ?, ?)");
    $mentes->bind_param("siisis", $nev, $idotartam, $osszsuly, $datum, $felhasznaloId, $leiras);
}

if (!$mentes->execute()) {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a mentéskor."]);
    exit;
}

$edzesId = (int)$conn->insert_id;

// Poszt létrehozása
$nevLek = $conn->prepare("SELECT nev FROM felhasznalo WHERE id = ?");
$nevLek->bind_param("i", $felhasznaloId);
$nevLek->execute();
$felhasznaloNev = $nevLek->get_result()->fetch_assoc()["nev"] ?? "Felhasználó";
$idoSzoveg = gmdate("H:i:s", $idotartam);
$posztTartalom = $felhasznaloNev . " befejezett egy edzést: " . $nev . " (" . $idoSzoveg . ")";

$posztMentes = $conn->prepare("INSERT INTO poszt (felhasznaloId, tartalom, edzesId) VALUES (?, ?, ?)");
$posztMentes->bind_param("isi", $felhasznaloId, $posztTartalom, $edzesId);
$posztMentes->execute();

echo json_encode(["siker" => true, "redirect" => "index.php"]);
