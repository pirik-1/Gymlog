<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Jelentkezz be a törléshez."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$tervId = (int)($json["terv_id"] ?? 0);

if ($tervId <= 0) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen terv."]);
    exit;
}

$userId = (int)$_SESSION["user_id"];
$stmt = $conn->prepare("DELETE FROM edzesterv_mentes WHERE id = ? AND felhasznaloId = ?");
$stmt->bind_param("ii", $tervId, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["siker" => true, "uzenet" => "Edzésterv törölve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Nem sikerült törölni, vagy nem a Te terved."]);
}
