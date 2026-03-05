<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

$datum = trim($_GET["datum"] ?? "");
$megtekintettId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
$userId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : 0;
$sajatProfil = ($megtekintettId <= 0 || $megtekintettId === $userId);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
    echo json_encode(["siker" => false, "edzesek" => []]);
    exit;
}

$profilUserId = ($megtekintettId > 0) ? $megtekintettId : $userId;
if ($profilUserId <= 0) {
    echo json_encode(["siker" => false, "edzesek" => []]);
    exit;
}

$tervId = isset($_GET["terv"]) ? (int)$_GET["terv"] : null;
$tervNev = null;
if ($tervId && $sajatProfil && $userId > 0) {
    $tervek = getTervek($conn, $userId);
    foreach ($tervek as $t) {
        if ((int)$t["id"] === $tervId) { $tervNev = $t["nev"] ?? ""; break; }
    }
}
if (!$tervNev) $tervId = null;

$edzesek = getEdzesekNap($conn, $profilUserId, $datum, $tervId, $tervNev);
echo json_encode(["siker" => true, "datum" => $datum, "edzesek" => $edzesek]);
