<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Bejelentkezés szükséges."]);
    exit;
}

$isAdmin = ($_SESSION["role"] ?? "") === "admin";
if (!$isAdmin) {
    echo json_encode(["siker" => false, "uzenet" => "Nincs jogosultság a törléshez."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$kommentId = (int)($json["komment_id"] ?? 0);

if ($kommentId <= 0) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen komment."]);
    exit;
}

if (kommentTorles($conn, $kommentId, (int)$_SESSION["user_id"], true)) {
    echo json_encode(["siker" => true, "uzenet" => "Komment törölve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a törléskor."]);
}
