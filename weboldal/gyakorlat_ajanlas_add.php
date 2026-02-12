<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$nev = trim($json["nev"] ?? "");

if ($nev === "" || mb_strlen($nev) > 100) {
    echo json_encode(["siker" => false, "uzenet" => "Adj meg egy gyakorlatnevet (max 100 karakter)."]);
    exit;
}

if (gyakorlatAjanlasBeszuras($conn, (int)$_SESSION["user_id"], $nev)) {
    echo json_encode(["siker" => true, "uzenet" => "Beajánlás elküldve. Adminisztrátor jóváhagyása szükséges."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a mentéskor."]);
}
