<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

$isAdmin = isset($_SESSION["user_id"]) && ($_SESSION["role"] ?? "") === "admin";
if (!$isAdmin) {
    echo json_encode(["siker" => false, "uzenet" => "Nincs jogosultság."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$id = (int)($json["id"] ?? 0);
$status = $json["status"] ?? "";

if ($id <= 0 || !in_array($status, ["approved", "rejected"])) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen adat."]);
    exit;
}

if (gyakorlatAjanlasStatusModosit($conn, $id, $status, true)) {
    echo json_encode(["siker" => true, "uzenet" => $status === "approved" ? "Jóváhagyva." : "Elutasítva."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a módosításkor."]);
}
