<?php
// Helper függvények

function getTervAdatok($conn, $tervId, $userId) {
    $check = $conn->query("SHOW TABLES LIKE 'edzesterv_mentes'");
    if (!$check || $check->num_rows === 0) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT nev, tartalom FROM edzesterv_mentes WHERE id = ? AND felhasznaloId = ?");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("ii", $tervId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            "nev" => $row["nev"],
            "tartalom" => json_decode($row["tartalom"], true) ?: []
        ];
    }
    
    return null;
}

function getTervek($conn, $userId) {
    $tervek = [];
    $check = $conn->query("SHOW TABLES LIKE 'edzesterv_mentes'");
    
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("SELECT id, nev, tartalom, letrehozva FROM edzesterv_mentes WHERE felhasznaloId = ? ORDER BY letrehozva DESC");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $tervek[] = $row;
            }
        }
    }
    
    return $tervek;
}

function getPosztok($conn, $limit = 50, $userId = null, $csakBaratok = false) {
    $posztok = [];
    $check = $conn->query("SHOW TABLES LIKE 'poszt'");
    if (!$check || $check->num_rows === 0) {
        return $posztok;
    }
    $hasEdzesId = false;
    $cols = $conn->query("SHOW COLUMNS FROM poszt LIKE 'edzesId'");
    if ($cols && $cols->num_rows > 0) {
        $hasEdzesId = true;
    }
    $sel = $hasEdzesId 
        ? "SELECT p.id, p.tartalom, p.datum, p.felhasznaloId, p.edzesId, f.nev as felhasznaloNev"
        : "SELECT p.id, p.tartalom, p.datum, p.felhasznaloId, NULL as edzesId, f.nev as felhasznaloNev";
    $where = "";
    $types = "";
    $params = [];
    if ($csakBaratok && $userId !== null) {
        $baratok = getBaratok($conn, (int)$userId);
        $engedelyezettIds = [(int)$userId];
        foreach ($baratok as $b) {
            $engedelyezettIds[] = (int)$b["id"];
        }
        $placeholders = implode(",", array_fill(0, count($engedelyezettIds), "?"));
        $where = " WHERE p.felhasznaloId IN (" . $placeholders . ")";
        $types = str_repeat("i", count($engedelyezettIds));
        $params = $engedelyezettIds;
    }
    $limitVal = (int)$limit;
    if (!empty($params)) {
        $stmt = $conn->prepare($sel . " FROM poszt p JOIN felhasznalo f ON f.id = p.felhasznaloId" . $where . " ORDER BY p.datum DESC LIMIT ?");
        if ($stmt) {
            $params[] = $limitVal;
            $types .= "i";
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $posztok[] = $row;
            }
        }
    } else {
        $res = $conn->query($sel . " FROM poszt p JOIN felhasznalo f ON f.id = p.felhasznaloId" . $where . " ORDER BY p.datum DESC LIMIT " . $limitVal);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $posztok[] = $row;
            }
        }
    }
    return $posztok;
}

function ensureBaratsagTable($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS baratsag (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        kero_id INT(11) NOT NULL,
        fogado_id INT(11) NOT NULL,
        status ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
        datum DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_keres (kero_id, fogado_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function getFelhasznalok($conn, $userId, $keres = "") {
    $felhasznalok = [];
    $keres = trim($keres);
    $sql = "SELECT id, nev, email FROM felhasznalo WHERE id != ?";
    $params = [$userId];
    $types = "i";
    if ($keres !== "") {
        $sql .= " AND (nev LIKE ? OR email LIKE ?)";
        $p = "%" . $keres . "%";
        $params[] = $p;
        $params[] = $p;
        $types .= "ss";
    }
    $sql .= " ORDER BY nev";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return $felhasznalok;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $felhasznalok[] = $row;
    }
    return $felhasznalok;
}

function getBaratiKerelmek($conn, $fogadoId) {
    $kerelmek = [];
    $check = $conn->query("SHOW TABLES LIKE 'baratsag'");
    if (!$check || $check->num_rows === 0) return $kerelmek;
    $stmt = $conn->prepare("SELECT b.id, b.kero_id, f.nev as kero_nev FROM baratsag b JOIN felhasznalo f ON f.id = b.kero_id WHERE b.fogado_id = ? AND b.status = 'pending' ORDER BY b.datum DESC");
    if (!$stmt) return $kerelmek;
    $stmt->bind_param("i", $fogadoId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $kerelmek[] = $row;
    }
    return $kerelmek;
}

function getBaratok($conn, $userId) {
    $baratok = [];
    $check = $conn->query("SHOW TABLES LIKE 'baratsag'");
    if (!$check || $check->num_rows === 0) return $baratok;
    $stmt = $conn->prepare("SELECT f.id, f.nev FROM baratsag b JOIN felhasznalo f ON (f.id = b.kero_id OR f.id = b.fogado_id) AND f.id != ? WHERE (b.kero_id = ? OR b.fogado_id = ?) AND b.status = 'accepted' ORDER BY f.nev");
    if (!$stmt) return $baratok;
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $baratok[] = $row;
    }
    return $baratok;
}

function getBaratsagAllapot($conn, $userId, $masikId) {
    $check = $conn->query("SHOW TABLES LIKE 'baratsag'");
    if (!$check || $check->num_rows === 0) return null;
    $stmt = $conn->prepare("SELECT status, kero_id FROM baratsag WHERE (kero_id = ? AND fogado_id = ?) OR (kero_id = ? AND fogado_id = ?)");
    if (!$stmt) return null;
    $stmt->bind_param("iiii", $userId, $masikId, $masikId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row;
    }
    return null;
}

function getFelhasznaloById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, nev, email FROM felhasznalo WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getProfilStat($conn, $userId, $tipus) {
    if ($tipus === 'edzes') {
        $r = $conn->query("SELECT COUNT(*) as c FROM edzes WHERE felhasznaloId = " . (int)$userId);
        return $r ? (int)$r->fetch_assoc()["c"] : 0;
    }
    if ($tipus === 'barat') {
        return count(getBaratok($conn, $userId));
    }
    return 0;
}

function formatGyakorlatReszletek($sor) {
    if (isset($sor["szettek"]) && is_array($sor["szettek"])) {
        $reszletek = [];
        foreach ($sor["szettek"] as $i => $sz) {
            $rep = (int)($sz["rep"] ?? 0);
            $suly = (int)($sz["suly"] ?? 0);
            $txt = $rep > 0 ? $rep . " ismétlés" : "";
            if ($suly > 0) $txt .= ($txt ? " / " : "") . $suly . " kg";
            if ($txt) $reszletek[] = ($i + 1) . ". " . $txt;
        }
        return !empty($reszletek) ? " – " . implode(", ", $reszletek) : "";
    }
    $set = isset($sor["set"]) ? (int)$sor["set"] : 0;
    $rep = isset($sor["rep"]) ? (int)$sor["rep"] : 0;
    $suly = isset($sor["suly"]) ? (int)$sor["suly"] : 0;
    $reszletek = [];
    if ($set > 0)  $reszletek[] = $set . "x";
    if ($rep > 0)  $reszletek[] = $rep . " ismétlés";
    if ($suly > 0) $reszletek[] = $suly . " kg";
    return !empty($reszletek) ? " – " . implode(", ", $reszletek) : "";
}
?>
