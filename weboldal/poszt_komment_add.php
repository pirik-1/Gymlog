<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Bejelentkezés szükséges."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$posztId = (int)($json["poszt_id"] ?? 0);
$tartalom = trim((string)($json["tartalom"] ?? ""));

if ($posztId <= 0 || $tartalom === "") {
    echo json_encode(["siker" => false, "uzenet" => "Hiányzó vagy érvénytelen adat."]);
    exit;
}

if (kommentHozzaad($conn, $posztId, (int)$_SESSION["user_id"], $tartalom)) {
    $uj = $conn->insert_id;
    $lek = $conn->prepare("SELECT k.id, k.tartalom, k.datum, f.nev as felhasznaloNev FROM komment k JOIN felhasznalo f ON f.id = k.felhasznaloId WHERE k.id = ?");
    $lek->bind_param("i", $uj);
    $lek->execute();
    $sor = $lek->get_result()->fetch_assoc();
    echo json_encode([
        "siker" => true,
        "komment" => $sor ? ["id" => (int)$sor["id"], "tartalom" => $sor["tartalom"], "datum" => $sor["datum"], "felhasznaloNev" => $sor["felhasznaloNev"]] : null
    ]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a komment hozzáadásakor."]);
}
