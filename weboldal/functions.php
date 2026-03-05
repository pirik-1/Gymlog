<?php
// Egyszerű segédfüggvények

function tervLekeres($conn, $tervId, $felhasznaloId) {
    $lekerdezes = $conn->prepare("SELECT nev, tartalom FROM edzesterv_mentes WHERE id = ? AND felhasznaloId = ?");
    $lekerdezes->bind_param("ii", $tervId, $felhasznaloId);
    $lekerdezes->execute();
    $sor = $lekerdezes->get_result()->fetch_assoc();
    if (!$sor) return null;
    return ["nev" => $sor["nev"], "tartalom" => json_decode($sor["tartalom"], true) ?: []];
}

function tervekLekeres($conn, $felhasznaloId) {
    $lista = [];
    $lekerdezes = $conn->prepare("SELECT id, nev, tartalom, letrehozva FROM edzesterv_mentes WHERE felhasznaloId = ? ORDER BY letrehozva DESC");
    $lekerdezes->bind_param("i", $felhasznaloId);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function posztokLekeres($conn, $limit, $felhasznaloId = null, $csakBaratok = false) {
    $lista = [];
    $limit = (int)$limit;
    if ($csakBaratok && $felhasznaloId) {
        $baratok = baratokLekeres($conn, $felhasznaloId);
        $engedelyezett = [$felhasznaloId];
        foreach ($baratok as $b) $engedelyezett[] = $b["id"];
        $helyek = implode(",", array_fill(0, count($engedelyezett), "?"));
        $tipus = str_repeat("i", count($engedelyezett)) . "i";
        $lekerdezes = $conn->prepare("SELECT p.id, p.tartalom, p.datum, p.felhasznaloId, p.edzesId, f.nev as felhasznaloNev FROM poszt p JOIN felhasznalo f ON f.id = p.felhasznaloId WHERE p.felhasznaloId IN ($helyek) ORDER BY p.datum DESC LIMIT ?");
        $params = array_merge($engedelyezett, [$limit]);
        $lekerdezes->bind_param($tipus, ...$params);
    } else {
        $lekerdezes = $conn->prepare("SELECT p.id, p.tartalom, p.datum, p.felhasznaloId, p.edzesId, f.nev as felhasznaloNev FROM poszt p JOIN felhasznalo f ON f.id = p.felhasznaloId ORDER BY p.datum DESC LIMIT ?");
        $lekerdezes->bind_param("i", $limit);
    }
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function gyakorlatAjanlasBeszur($conn, $felhasznaloId, $nev) {
    $lekerdezes = $conn->prepare("INSERT INTO gyakorlat_ajanlas (felhasznalo_id, nev) VALUES (?, ?)");
    $lekerdezes->bind_param("is", $felhasznaloId, $nev);
    return $lekerdezes->execute();
}

function sajatAjanlasok($conn, $felhasznaloId) {
    $lista = [];
    $lekerdezes = $conn->prepare("SELECT id, nev, status, datum FROM gyakorlat_ajanlas WHERE felhasznalo_id = ? ORDER BY datum DESC");
    $lekerdezes->bind_param("i", $felhasznaloId);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function fuggoAjanlasok($conn) {
    $lista = [];
    $eredmeny = $conn->query("SELECT a.id, a.nev, a.datum, f.nev as felhasznalo_nev FROM gyakorlat_ajanlas a JOIN felhasznalo f ON f.id = a.felhasznalo_id WHERE a.status = 'pending' ORDER BY a.datum ASC");
    if ($eredmeny) while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function ajanlasStatus($conn, $ajanlasId, $status, $admin) {
    if (!$admin || !in_array($status, ['approved', 'rejected'])) return false;
    $lekerdezes = $conn->prepare("UPDATE gyakorlat_ajanlas SET status = ? WHERE id = ?");
    $lekerdezes->bind_param("si", $status, $ajanlasId);
    return $lekerdezes->execute();
}

function jovahagyottGyakorlatok($conn) {
    $lista = [];
    $eredmeny = $conn->query("SELECT DISTINCT nev FROM gyakorlat_ajanlas WHERE status = 'approved' ORDER BY nev");
    if ($eredmeny) while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor["nev"];
    return $lista;
}

function jovahagyottAjanlasokLista($conn) {
    $lista = [];
    $eredmeny = $conn->query("SELECT a.id, a.nev, a.datum, f.nev as felhasznalo_nev FROM gyakorlat_ajanlas a JOIN felhasznalo f ON f.id = a.felhasznalo_id WHERE a.status = 'approved' ORDER BY a.nev, a.datum");
    if ($eredmeny) while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function baratiKerelmek($conn, $fogadoId) {
    $lista = [];
    $lekerdezes = $conn->prepare("SELECT b.id, b.kero_id, f.nev as kero_nev FROM baratsag b JOIN felhasznalo f ON f.id = b.kero_id WHERE b.fogado_id = ? AND b.status = 'pending' ORDER BY b.datum DESC");
    $lekerdezes->bind_param("i", $fogadoId);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function gyakorlatokLekeres($conn) {
    $alap = ["Arnold press", "Bicepsz curl", "Döntött pad fekvenyomás", "Egykezes sor", "Fej fölé nyomás", "Fekvenyomás", "Felhúzás", "Felhúzás román", "Francia nyomás", "Guggolás", "Hamstring curl", "Húzódzkodás", "Kalapács hajlítás", "Lefekvő lenyomás", "Mellgépen fekvés", "Oldalemelés", "Rudat evezés", "Shrug", "Tolódzkodás", "Tricepsz letolás", "Vállból nyomás"];
    $extra = jovahagyottGyakorlatok($conn);
    $osszes = array_unique(array_merge($alap, $extra));
    sort($osszes);
    return $osszes;
}

function felhasznalokLekeres($conn, $kijelentkezettId, $keres) {
    $lista = [];
    if ($keres !== "") {
        $minta = "%" . $keres . "%";
        $lekerdezes = $conn->prepare("SELECT id, nev, email FROM felhasznalo WHERE id != ? AND (nev LIKE ? OR email LIKE ?) ORDER BY nev");
        $lekerdezes->bind_param("iss", $kijelentkezettId, $minta, $minta);
    } else {
        $lekerdezes = $conn->prepare("SELECT id, nev, email FROM felhasznalo WHERE id != ? ORDER BY nev");
        $lekerdezes->bind_param("i", $kijelentkezettId);
    }
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function baratokLekeres($conn, $felhasznaloId) {
    $lista = [];
    $lekerdezes = $conn->prepare("SELECT f.id, f.nev FROM baratsag b JOIN felhasznalo f ON (f.id = b.kero_id OR f.id = b.fogado_id) AND f.id != ? WHERE (b.kero_id = ? OR b.fogado_id = ?) AND b.status = 'accepted' ORDER BY f.nev");
    $lekerdezes->bind_param("iii", $felhasznaloId, $felhasznaloId, $felhasznaloId);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function baratsagAllapot($conn, $felhasznaloId, $masikId) {
    $lekerdezes = $conn->prepare("SELECT status, kero_id FROM baratsag WHERE (kero_id = ? AND fogado_id = ?) OR (kero_id = ? AND fogado_id = ?)");
    $lekerdezes->bind_param("iiii", $felhasznaloId, $masikId, $masikId, $felhasznaloId);
    $lekerdezes->execute();
    return $lekerdezes->get_result()->fetch_assoc();
}

function felhasznaloLekeres($conn, $id) {
    $lekerdezes = $conn->prepare("SELECT id, nev, email, magassag, testsuly, nem FROM felhasznalo WHERE id = ?");
    $lekerdezes->bind_param("i", $id);
    $lekerdezes->execute();
    return $lekerdezes->get_result()->fetch_assoc();
}

function profilStat($conn, $felhasznaloId, $tipus) {
    if ($tipus === "edzes") {
        $eredmeny = $conn->query("SELECT COUNT(*) as db FROM edzes WHERE felhasznaloId = " . (int)$felhasznaloId);
        return $eredmeny ? (int)$eredmeny->fetch_assoc()["db"] : 0;
    }
    if ($tipus === "barat") return count(baratokLekeres($conn, $felhasznaloId));
    return 0;
}

function profilEdzesek($conn, $felhasznaloId, $limit, $honap, $tervId, $tervNev) {
    $lista = [];
    $hol = ["felhasznaloId = ?"];
    $params = [$felhasznaloId];
    $tipus = "i";
    if ($honap && $honap !== "osszes") {
        $kezdete = $honap . "-01";
        $vege = $honap . "-" . date("t", strtotime($kezdete . " 12:00:00"));
        $hol[] = "datum >= ?";
        $hol[] = "datum <= ?";
        $params[] = $kezdete;
        $params[] = $vege;
        $tipus .= "ss";
    }
    if ($tervId && $tervNev) {
        $hol[] = "(edzestervMentesId = ? OR (edzestervMentesId IS NULL AND nev = ?))";
        $params[] = $tervId;
        $params[] = $tervNev;
        $tipus .= "is";
    }
    $sql = "SELECT id, nev, datum, idotartam, osszsuly FROM edzes WHERE " . implode(" AND ", $hol) . " ORDER BY datum DESC, id DESC";
    if (!$honap || $honap === "osszes") {
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;
        $tipus .= "i";
    }
    $lekerdezes = $conn->prepare($sql);
    $lekerdezes->bind_param($tipus, ...$params);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function edzesHonapok($conn, $felhasznaloId, $tervId, $tervNev, $limit) {
    $lista = [];
    $sql = "SELECT DISTINCT DATE_FORMAT(datum, '%Y-%m') as honap FROM edzes WHERE felhasznaloId = ?";
    $params = [$felhasznaloId];
    $tipus = "i";
    if ($tervId && $tervNev) {
        $sql .= " AND (edzestervMentesId = ? OR (edzestervMentesId IS NULL AND nev = ?))";
        $params[] = $tervId;
        $params[] = $tervNev;
        $tipus .= "is";
    }
    $sql .= " ORDER BY honap DESC LIMIT ?";
    $params[] = (int)$limit;
    $tipus .= "i";
    $lekerdezes = $conn->prepare($sql);
    $lekerdezes->bind_param($tipus, ...$params);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor["honap"];
    return $lista;
}

function edzesNapok($conn, $felhasznaloId, $honap, $tervId, $tervNev) {
    $lista = [];
    if (!$honap) $honap = date("Y-m");
    $kezdete = $honap . "-01";
    $vege = $honap . "-" . date("t", strtotime($kezdete . " 12:00:00"));
    $sql = "SELECT DISTINCT DATE(datum) as nap FROM edzes WHERE felhasznaloId = ? AND datum >= ? AND datum <= ?";
    $params = [$felhasznaloId, $kezdete, $vege];
    $tipus = "iss";
    if ($tervId && $tervNev) {
        $sql .= " AND (edzestervMentesId = ? OR (edzestervMentesId IS NULL AND nev = ?))";
        $params[] = $tervId;
        $params[] = $tervNev;
        $tipus .= "is";
    }
    $lekerdezes = $conn->prepare($sql);
    $lekerdezes->bind_param($tipus, ...$params);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor["nap"];
    return $lista;
}

function edzesekNap($conn, $felhasznaloId, $datum, $tervId, $tervNev) {
    $lista = [];
    $sql = "SELECT id, nev, datum, idotartam, osszsuly FROM edzes WHERE felhasznaloId = ? AND DATE(datum) = ?";
    $params = [$felhasznaloId, $datum];
    $tipus = "is";
    if ($tervId && $tervNev) {
        $sql .= " AND (edzestervMentesId = ? OR (edzestervMentesId IS NULL AND nev = ?))";
        $params[] = $tervId;
        $params[] = $tervNev;
        $tipus .= "is";
    }
    $sql .= " ORDER BY id";
    $lekerdezes = $conn->prepare($sql);
    $lekerdezes->bind_param($tipus, ...$params);
    $lekerdezes->execute();
    $eredmeny = $lekerdezes->get_result();
    while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function gyakorlatSzoveg($sor) {
    if (isset($sor["szettek"]) && is_array($sor["szettek"])) {
        $reszletek = [];
        foreach ($sor["szettek"] as $i => $sz) {
            $rep = (int)($sz["rep"] ?? 0);
            $suly = (int)($sz["suly"] ?? 0);
            $kesz = !empty($sz["kesz"]);
            $txt = $rep > 0 ? $rep . " ismétlés" : "";
            if ($suly > 0) $txt .= ($txt ? " / " : "") . $suly . " kg";
            if ($txt) $reszletek[] = ($i + 1) . ". " . ($kesz ? "✓ " : "") . $txt;
        }
        return !empty($reszletek) ? " – " . implode(", ", $reszletek) : "";
    }
    $set = (int)($sor["set"] ?? 0);
    $rep = (int)($sor["rep"] ?? 0);
    $suly = (int)($sor["suly"] ?? 0);
    $reszletek = [];
    if ($set > 0) $reszletek[] = $set . "x";
    if ($rep > 0) $reszletek[] = $rep . " ismétlés";
    if ($suly > 0) $reszletek[] = $suly . " kg";
    return !empty($reszletek) ? " – " . implode(", ", $reszletek) : "";
}

// Régi függvénynevek (kompatibilitás)
function gyakorlatAjanlasBeszuras($c, $uid, $n) { return gyakorlatAjanlasBeszur($c, $uid, $n); }
function getSajatGyakorlatAjanlasok($c, $uid) { return sajatAjanlasok($c, $uid); }
function getFuggoGyakorlatAjanlasok($c) { return fuggoAjanlasok($c); }
function gyakorlatAjanlasStatusModosit($c, $id, $s, $a) { return ajanlasStatus($c, $id, $s, $a); }
function getJovahagyottGyakorlatok($c) { return jovahagyottGyakorlatok($c); }
function getJovahagyottAjanlasokLista($c) { return jovahagyottAjanlasokLista($c); }
function getBaratiKerelmek($c, $fid) { return baratiKerelmek($c, $fid); }
function getTervAdatok($c, $tid, $uid) { return tervLekeres($c, $tid, $uid); }
function getTervek($c, $uid) { return tervekLekeres($c, $uid); }
function getPosztok($c, $l, $uid = null, $cb = false) { return posztokLekeres($c, $l, $uid, $cb); }

function kommentekLekeres($conn, $posztIds) {
    if (empty($posztIds)) return [];
    $helyek = implode(",", array_map("intval", $posztIds));
    $lista = [];
    $eredmeny = $conn->query("SELECT k.id, k.posztId, k.felhasznaloId, k.tartalom, k.datum, f.nev as felhasznaloNev FROM komment k JOIN felhasznalo f ON f.id = k.felhasznaloId WHERE k.posztId IN ($helyek) ORDER BY k.datum ASC");
    if ($eredmeny) while ($sor = $eredmeny->fetch_assoc()) $lista[] = $sor;
    return $lista;
}

function kommentHozzaad($conn, $posztId, $felhasznaloId, $tartalom) {
    $tartalom = trim($tartalom);
    if ($tartalom === "" || strlen($tartalom) > 500) return false;
    $posztId = (int)$posztId;
    $felhasznaloId = (int)$felhasznaloId;
    $lekerdezes = $conn->prepare("SELECT id FROM poszt WHERE id = ?");
    $lekerdezes->bind_param("i", $posztId);
    $lekerdezes->execute();
    if (!$lekerdezes->get_result()->fetch_assoc()) return false;
    $beszur = $conn->prepare("INSERT INTO komment (posztId, felhasznaloId, tartalom) VALUES (?, ?, ?)");
    $beszur->bind_param("iis", $posztId, $felhasznaloId, $tartalom);
    return $beszur->execute();
}

function kommentTorles($conn, $kommentId, $felhasznaloId, $isAdmin) {
    if (!$isAdmin) return false;
    $kommentId = (int)$kommentId;
    $torles = $conn->prepare("DELETE FROM komment WHERE id = ?");
    $torles->bind_param("i", $kommentId);
    return $torles->execute();
}
function getOsszesGyakorlat($c) { return gyakorlatokLekeres($c); }
function getFelhasznalok($c, $uid, $k = "") { return felhasznalokLekeres($c, $uid, $k); }
function getBaratok($c, $uid) { return baratokLekeres($c, $uid); }
function getBaratsagAllapot($c, $uid, $mid) { return baratsagAllapot($c, $uid, $mid); }
function getFelhasznaloById($c, $id) { return felhasznaloLekeres($c, $id); }
function getProfilStat($c, $uid, $t) { return profilStat($c, $uid, $t); }
function getProfilEdzesek($c, $uid, $l, $h, $tid = null, $tn = null) { return profilEdzesek($c, $uid, $l, $h, $tid, $tn); }
function getEdzesHonapok($c, $uid, $tid = null, $tn = null, $l = 24) { return edzesHonapok($c, $uid, $tid, $tn, $l); }
function getEdzesNapokHonap($c, $uid, $h = null, $tid = null, $tn = null) { return edzesNapok($c, $uid, $h, $tid, $tn); }
function getEdzesekNap($c, $uid, $d, $tid = null, $tn = null) { return edzesekNap($c, $uid, $d, $tid, $tn); }
function formatGyakorlatReszletek($s) { return gyakorlatSzoveg($s); }
