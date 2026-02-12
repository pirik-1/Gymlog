<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false]);
    exit;
}

$nev = trim($_GET["gyakorlat_nev"] ?? $_POST["gyakorlat_nev"] ?? "");
if ($nev === "") {
    echo json_encode(["siker" => false]);
    exit;
}

$userId = (int)$_SESSION["user_id"];

$check = $conn->query("SHOW TABLES LIKE 'edzes'");
if (!$check || $check->num_rows === 0) {
    echo json_encode(["siker" => false]);
    exit;
}

$stmt = $conn->prepare("SELECT leiras, datum FROM edzes WHERE felhasznaloId = ? ORDER BY datum DESC, id DESC");
if (!$stmt) {
    echo json_encode(["siker" => false]);
    exit;
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $sorok = json_decode($row["leiras"], true);
    if (!is_array($sorok)) continue;
    foreach ($sorok as $s) {
        $sorNev = trim($s["nev"] ?? "");
        if (mb_strtolower($sorNev) !== mb_strtolower($nev)) continue;
        if (isset($s["szettek"]) && is_array($s["szettek"])) {
            $szettek = [];
            foreach ($s["szettek"] as $sz) {
                $szettek[] = [
                    "rep" => (int)($sz["rep"] ?? 8),
                    "suly" => (int)($sz["suly"] ?? 0)
                ];
            }
            if (!empty($szettek)) {
                echo json_encode(["siker" => true, "szettek" => $szettek]);
                exit;
            }
        }
        $set = (int)($s["set"] ?? 3);
        $rep = (int)($s["rep"] ?? 8);
        $suly = (int)($s["suly"] ?? 0);
        $szettek = [];
        for ($i = 0; $i < max(1, $set); $i++) {
            $szettek[] = ["rep" => $rep, "suly" => $suly];
        }
        echo json_encode(["siker" => true, "szettek" => $szettek]);
        exit;
    }
}

echo json_encode(["siker" => false]);
