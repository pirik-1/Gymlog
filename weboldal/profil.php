<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;

$megtekintettId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
$sajatProfil = ($megtekintettId <= 0 || $megtekintettId === $userId);
$profilUser = null;

if ($sajatProfil && $bejelentkezve) {
    $profilUser = getFelhasznaloById($conn, $userId);
} elseif ($megtekintettId > 0) {
    $profilUser = getFelhasznaloById($conn, $megtekintettId);
}

$baratsagAllapot = null;
$baratok = [];
$edzesDb = 0;
$baratDb = 0;
$edzesek = [];
$edzesNapok = [];
$valasztottHonap = null;
$honapNevek = ["", "január", "február", "március", "április", "május", "június", "július", "augusztus", "szeptember", "október", "november", "december"];

// Honap param: YYYY-MM, "osszes", max mai hónap
$honapParam = trim($_GET["honap"] ?? "");
if ($honapParam === "osszes") {
    $valasztottHonap = "osszes";
} elseif (preg_match('/^\d{4}-\d{2}$/', $honapParam)) {
    $honapTs = strtotime($honapParam . "-01");
    $maHonap = date("Y-m");
    if ($honapTs && date("Y-m", $honapTs) <= $maHonap) {
        $valasztottHonap = date("Y-m", $honapTs);
    }
}
if (!$valasztottHonap) {
    $valasztottHonap = date("Y-m");
}
$tervParam = isset($_GET["terv"]) ? (int)$_GET["terv"] : 0;
$valasztottTervId = $tervParam > 0 ? $tervParam : null;
$tervekLista = [];
$valasztottTervNev = null;

if ($profilUser) {
    $edzesDb = getProfilStat($conn, $profilUser["id"], "edzes");
    $baratDb = getProfilStat($conn, $profilUser["id"], "barat");
    $tervekLista = ($sajatProfil && $bejelentkezve) ? getTervek($conn, $userId) : [];
    $valasztottTervNev = null;
    if ($valasztottTervId && !empty($tervekLista)) {
        foreach ($tervekLista as $t) {
            if ((int)$t["id"] === $valasztottTervId) { $valasztottTervNev = $t["nev"] ?? ""; break; }
        }
    }
    $edzesek = getProfilEdzesek($conn, $profilUser["id"], 200, $valasztottHonap, $valasztottTervId, $valasztottTervNev);
    $honapNaptarhoz = ($valasztottHonap === "osszes") ? date("Y-m") : $valasztottHonap;
    $edzesNapok = getEdzesNapokHonap($conn, $profilUser["id"], $honapNaptarhoz, $valasztottTervId, $valasztottTervNev);
    if ($sajatProfil && $bejelentkezve) {
        $baratok = getBaratok($conn, $userId);
    } else {
        $baratsagAllapot = $bejelentkezve ? getBaratsagAllapot($conn, $userId, $megtekintettId) : null;
    }
}

$nemSzoveg = ["ferfi" => "Férfi", "no" => "Nő", "mas" => "Egyéb"];

// Lapozó URL-ek és szűrők
$urlParams = [];
if ($megtekintettId > 0) $urlParams["user_id"] = $megtekintettId;
$honapNaptarhoz = ($valasztottHonap === "osszes") ? date("Y-m") : $valasztottHonap;
$honapPrevTs = strtotime($honapNaptarhoz . "-01 -1 month");
$honapNextTs = strtotime($honapNaptarhoz . "-01 +1 month");
$honapPrev = date("Y-m", $honapPrevTs);
$honapNext = date("Y-m", $honapNextTs);
$maHonap = date("Y-m");
$vanElozo = ($honapPrev <= $maHonap);
$vanKovetkezo = ($honapNext <= $maHonap);
$naptarHonapSzoveg = ($valasztottHonap === "osszes") 
    ? (date("Y", strtotime($honapNaptarhoz . "-01")) . ". " . $honapNevek[(int)date("n", strtotime($honapNaptarhoz . "-01"))])
    : (date("Y", strtotime($valasztottHonap . "-01")) . ". " . $honapNevek[(int)date("n", strtotime($valasztottHonap . "-01"))]);
$tervek = $tervekLista;
$edzesHonapok = $profilUser ? getEdzesHonapok($conn, $profilUser["id"], $valasztottTervId, $valasztottTervNev ?? null, 24) : [];
if ($valasztottHonap && $valasztottHonap !== "osszes" && !in_array($valasztottHonap, $edzesHonapok)) {
    array_unshift($edzesHonapok, $valasztottHonap);
}
$baseUrlParams = $urlParams;
if ($valasztottHonap) $baseUrlParams["honap"] = $valasztottHonap;
if ($valasztottTervId) $baseUrlParams["terv"] = $valasztottTervId;
function profilUrl($params, $overrides = []) {
    $p = array_merge($params, $overrides);
    foreach ($p as $k => $v) { if ($v === null || $v === "") unset($p[$k]); }
    return "profil.php" . (empty($p) ? "" : "?" . http_build_query($p));
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/profiltartalom.css">
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

<?php if (!$bejelentkezve): ?>
<main class="profil-main">
    <div class="profil-shell">
        <section class="profil-card profil-basic">
            <h1>Profil</h1>
            <p class="vendeg-uzenet">Jelentkezz be a profilok megtekintéséhez.</p>
            <a href="login-html.php" class="gomb">Bejelentkezés</a>
        </section>
    </div>
</main>
<?php elseif (!$profilUser): ?>
<main class="profil-main">
    <div class="profil-shell">
        <div class="profil-card profil-basic">
            <p>Felhasználó nem található.</p>
            <a href="kozosseg.php" class="gomb">Vissza a közösséghez</a>
        </div>
    </div>
</main>
<?php else: ?>
<main class="profil-main">
    <div class="profil-shell">
        <section class="profil-fo">
            <div class="profil-card profil-fejezet">
                <div class="fej-sor">
                    <div class="fej-bal">
                        <h1 class="profil-nev-sor">
                            <?php echo htmlspecialchars($profilUser["nev"]); ?>
                            <?php if (!empty($profilUser["nem"]) && $profilUser["nem"] === "no"): ?>
                                <span class="nem-ikon nem-no" title="Nő">♀</span>
                            <?php elseif (!empty($profilUser["nem"]) && $profilUser["nem"] === "ferfi"): ?>
                                <span class="nem-ikon nem-ferfi" title="Férfi">♂</span>
                            <?php endif; ?>
                        </h1>
                        <?php if (!$sajatProfil): ?>
                            <a href="kozosseg.php" class="vissza-kozosseg">← Vissza a közösséghez</a>
                            <p id="baratAllapotUzenet"></p>
                            <?php if ($baratsagAllapot === null): ?>
                                <button type="button" id="baratJonelolGomb" class="barat-jonelol-gomb" data-user-id="<?php echo (int)$profilUser["id"]; ?>">Barátnak jelölés</button>
                            <?php elseif ($baratsagAllapot["status"] === "pending"): ?>
                                <p class="barat-status"><?php echo (int)$baratsagAllapot["kero_id"] === $userId ? "Baráti kérés küldve." : "Fogadd el a Közösség oldalon."; ?></p>
                            <?php else: ?>
                                <p class="barat-status">Már barátok vagytok.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="fej-statok">
                        <div class="stat-chip"><span class="stat-szam"><?php echo $edzesDb; ?></span> edzés</div>
                        <div class="stat-chip"><span class="stat-szam"><?php echo $baratDb; ?></span> barát</div>
                    </div>
                </div>
            </div>

            <?php if ($sajatProfil): ?>
            <div class="profil-tartalom-grid">
            <div class="profil-mellék">
            <div class="profil-card profil-adatok">
                <h2>Személyes adatok <span class="privát-hint">(csak neked látható)</span></h2>
                <div class="profil-adatok-form">
                    <div class="form-sor">
                        <label>Magasság (cm)</label>
                        <input type="number" id="magassagInput" min="50" max="250" placeholder="pl. 175" value="<?php echo $profilUser["magassag"] ? (int)$profilUser["magassag"] : ""; ?>">
                    </div>
                    <div class="form-sor">
                        <label>Testsúly (kg)</label>
                        <input type="number" id="testsulyInput" min="20" max="300" placeholder="pl. 75" value="<?php echo $profilUser["testsuly"] ? (int)$profilUser["testsuly"] : ""; ?>">
                    </div>
                    <div class="form-sor">
                        <label>Nem</label>
                        <select id="nemSelect">
                            <option value="">—</option>
                            <option value="ferfi" <?php echo ($profilUser["nem"] ?? "") === "ferfi" ? "selected" : ""; ?>>Férfi</option>
                            <option value="no" <?php echo ($profilUser["nem"] ?? "") === "no" ? "selected" : ""; ?>>Nő</option>
                            <option value="mas" <?php echo ($profilUser["nem"] ?? "") === "mas" ? "selected" : ""; ?>>Egyéb</option>
                        </select>
                    </div>
                    <button type="button" id="adatokMentes" class="mentes-gomb-kicsi">Mentés</button>
                    <p id="adatokUzenet" class="form-uzenet"></p>
                </div>
            </div>

            <button type="button" id="kaloriaKalkulatorGomb" class="kaloria-kalkulator-gomb">Kalória kalkulátor</button>

            <div class="profil-card profil-naptar">
                <h2>Edzés napjai</h2>
                <div class="naptar-lapozo">
                    <?php if ($vanElozo): ?><a href="<?php echo htmlspecialchars(profilUrl($baseUrlParams, ["honap" => $honapPrev])); ?>" class="naptar-gomb" title="Előző hónap">←</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">←</span><?php endif; ?>
                    <p class="naptar-honap"><?php echo htmlspecialchars($naptarHonapSzoveg); ?></p>
                    <?php if ($vanKovetkezo): ?><a href="<?php echo htmlspecialchars(profilUrl($baseUrlParams, ["honap" => $honapNext])); ?>" class="naptar-gomb" title="Következő hónap">→</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">→</span><?php endif; ?>
                </div>
                <div class="naptar-grid">
                    <?php
                    $honapStartTs = strtotime($honapNaptarhoz . "-01");
                    $napokSzama = date("t", $honapStartTs);
                    for ($i = 1; $i <= $napokSzama; $i++):
                        $d = date("Y-m-d", mktime(0,0,0, (int)date("n", $honapStartTs), $i, (int)date("Y", $honapStartTs)));
                        $van = in_array($d, $edzesNapok);
                    ?>
                    <div class="naptar-nap <?php echo $van ? "edzett" : ""; ?>" data-datum="<?php echo htmlspecialchars($d); ?>" data-klikkelheto="<?php echo $van ? "1" : "0"; ?>" title="<?php echo $van ? "Edzett ezen a napon – kattints a részletekért" : ""; ?>"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                <p class="naptar-jelmagy">A kitöltött napok az edzéseket jelölik.</p>
            </div>
            </div>

            <div class="profil-card profil-edzesek profil-edzesek-fo">
                <h2>Edzéseim<?php echo $valasztottHonap !== "osszes" ? " – " . htmlspecialchars($naptarHonapSzoveg) : ""; ?></h2>
                <div class="edzes-szurok">
                    <div class="szuro-csoport">
                        <label class="szuro-cimke" for="szuro-honap">Hónap:</label>
                        <select id="szuro-honap" class="szuro-select" onchange="location.href=this.value">
                            <option value="<?php echo htmlspecialchars(profilUrl($urlParams, ["honap" => "osszes"] + ($valasztottTervId ? ["terv" => $valasztottTervId] : []))); ?>" <?php echo $valasztottHonap === "osszes" ? "selected" : ""; ?>>Összes</option>
                            <?php foreach ($edzesHonapok as $ym): 
                                $honapSzoveg = date("Y", strtotime($ym . "-01")) . ". " . $honapNevek[(int)date("n", strtotime($ym . "-01"))];
                                $linkUrl = profilUrl($urlParams, ["honap" => $ym] + ($valasztottTervId ? ["terv" => $valasztottTervId] : []));
                            ?>
                            <option value="<?php echo htmlspecialchars($linkUrl); ?>" <?php echo $valasztottHonap === $ym ? "selected" : ""; ?>><?php echo htmlspecialchars($honapSzoveg); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($tervek)): ?>
                    <div class="szuro-csoport">
                        <label class="szuro-cimke" for="szuro-terv">Terv:</label>
                        <select id="szuro-terv" class="szuro-select" onchange="location.href=this.value">
                            <option value="<?php echo htmlspecialchars(profilUrl($urlParams, ($valasztottHonap && $valasztottHonap !== "osszes" ? ["honap" => $valasztottHonap] : ["honap" => "osszes"]))); ?>">Összes</option>
                            <?php foreach ($tervek as $t): 
                                $linkUrl = profilUrl($urlParams, ["terv" => $t["id"]] + ($valasztottHonap && $valasztottHonap !== "osszes" ? ["honap" => $valasztottHonap] : ["honap" => "osszes"]));
                            ?>
                            <option value="<?php echo htmlspecialchars($linkUrl); ?>" <?php echo $valasztottTervId === (int)$t["id"] ? "selected" : ""; ?>><?php echo htmlspecialchars($t["nev"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (empty($edzesek)): ?>
                    <p class="ures-hint"><?php echo $valasztottTervId ? "Nincs befejezett edzésed ebben a tervben. Indíts egy edzést a tervből, fejezd be, majd itt megjelenik." : "Még nincs befejezett edzésed."; ?></p>
                <?php else: ?>
                    <ul class="edzes-lista">
                        <?php foreach ($edzesek as $e): ?>
                        <li>
                            <a href="edzes_reszletek.php?id=<?php echo (int)$e["id"]; ?>">
                                <span class="edzes-nev"><?php echo htmlspecialchars($e["nev"]); ?></span>
                                <span class="edzes-meta"><?php echo htmlspecialchars($e["datum"]); ?> • <?php echo gmdate("H:i:s", (int)$e["idotartam"]); ?> • <?php echo (int)$e["osszsuly"]; ?> kg</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            </div>
            <?php else: ?>
            <div class="profil-tartalom-grid">
            <div class="profil-card profil-edzesek profil-edzesek-fo">
                <h2>Edzései<?php echo $valasztottHonap !== "osszes" ? " – " . htmlspecialchars($naptarHonapSzoveg) : ""; ?></h2>
                <div class="edzes-szurok">
                    <div class="szuro-csoport">
                        <label class="szuro-cimke" for="szuro-honap-mas">Hónap:</label>
                        <select id="szuro-honap-mas" class="szuro-select" onchange="location.href=this.value">
                            <option value="<?php echo htmlspecialchars(profilUrl($urlParams, ["honap" => "osszes"])); ?>" <?php echo $valasztottHonap === "osszes" ? "selected" : ""; ?>>Összes</option>
                            <?php foreach ($edzesHonapok as $ym): 
                                $honapSzoveg = date("Y", strtotime($ym . "-01")) . ". " . $honapNevek[(int)date("n", strtotime($ym . "-01"))];
                                $linkUrl = profilUrl($urlParams, ["honap" => $ym]);
                            ?>
                            <option value="<?php echo htmlspecialchars($linkUrl); ?>" <?php echo $valasztottHonap === $ym ? "selected" : ""; ?>><?php echo htmlspecialchars($honapSzoveg); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php if (empty($edzesek)): ?>
                    <p class="ures-hint">Még nincs befejezett edzése.</p>
                <?php else: ?>
                    <ul class="edzes-lista">
                        <?php foreach ($edzesek as $e): ?>
                        <li>
                            <a href="edzes_reszletek.php?id=<?php echo (int)$e["id"]; ?>">
                                <span class="edzes-nev"><?php echo htmlspecialchars($e["nev"]); ?></span>
                                <span class="edzes-meta"><?php echo htmlspecialchars($e["datum"]); ?> • <?php echo gmdate("H:i:s", (int)$e["idotartam"]); ?> • <?php echo (int)$e["osszsuly"]; ?> kg</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="profil-mellék">
            <div class="profil-card profil-naptar">
                <h2>Edzés napjai</h2>
                <div class="naptar-lapozo">
                    <?php if ($vanElozo): ?><a href="<?php echo htmlspecialchars(profilUrl($baseUrlParams, ["honap" => $honapPrev])); ?>" class="naptar-gomb" title="Előző hónap">←</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">←</span><?php endif; ?>
                    <p class="naptar-honap"><?php echo htmlspecialchars($naptarHonapSzoveg); ?></p>
                    <?php if ($vanKovetkezo): ?><a href="<?php echo htmlspecialchars(profilUrl($baseUrlParams, ["honap" => $honapNext])); ?>" class="naptar-gomb" title="Következő hónap">→</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">→</span><?php endif; ?>
                </div>
                <div class="naptar-grid">
                    <?php
                    $honapStartTsMas = strtotime($honapNaptarhoz . "-01");
                    $napokSzamaMas = date("t", $honapStartTsMas);
                    for ($i = 1; $i <= $napokSzamaMas; $i++):
                        $d = date("Y-m-d", mktime(0,0,0, (int)date("n", $honapStartTsMas), $i, (int)date("Y", $honapStartTsMas)));
                        $van = in_array($d, $edzesNapok);
                    ?>
                    <div class="naptar-nap <?php echo $van ? "edzett" : ""; ?>" data-datum="<?php echo htmlspecialchars($d); ?>" data-klikkelheto="<?php echo $van ? "1" : "0"; ?>" title="<?php echo $van ? "Edzett ezen a napon – kattints a részletekért" : ""; ?>"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                <p class="naptar-jelmagy">A kitöltött napok az edzéseket jelölik.</p>
            </div>
            </div>
            </div>
            <?php endif; ?>
        </section>

        <aside class="profil-oldal">
            <div class="profil-card profil-friends">
                <h2>Barátok</h2>
                <?php if ($sajatProfil): ?>
                    <ul class="friends-list">
                        <?php foreach ($baratok as $b): ?>
                            <li><a href="profil.php?user_id=<?php echo (int)$b["id"]; ?>"><?php echo htmlspecialchars($b["nev"]); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (empty($baratok)): ?>
                        <p class="friends-hint">Itt jelennek meg a barátaid. Jelöld barátnak másokat a Közösség oldalon.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="friends-hint">A barátok listája csak a saját profilban látható.</p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>

<?php if ($sajatProfil): ?>
<div id="kaloriaPopup" class="popup-overlay">
    <div class="popup-kalkulator">
        <button type="button" class="popup-close" aria-label="Bezárás">×</button>
        <h2>Kalória kalkulátor</h2>
        <div class="kalkulator-form">
            <div class="form-sor">
                <label>Életkor</label>
                <input type="number" id="kalkEletkor" min="10" max="120" placeholder="pl. 30">
            </div>
            <div class="form-sor">
                <label>Magasság (cm)</label>
                <input type="number" id="kalkMagassag" min="50" max="250" placeholder="pl. 175" value="<?php echo $profilUser["magassag"] ? (int)$profilUser["magassag"] : ""; ?>">
            </div>
            <div class="form-sor">
                <label>Testsúly (kg)</label>
                <input type="number" id="kalkTomeg" min="20" max="300" placeholder="pl. 75" value="<?php echo $profilUser["testsuly"] ? (int)$profilUser["testsuly"] : ""; ?>">
            </div>
            <div class="form-sor">
                <label>Nem</label>
                <select id="kalkNem">
                    <option value="">—</option>
                    <option value="ferfi" <?php echo ($profilUser["nem"] ?? "") === "ferfi" ? "selected" : ""; ?>>Férfi</option>
                    <option value="no" <?php echo ($profilUser["nem"] ?? "") === "no" ? "selected" : ""; ?>>Nő</option>
                </select>
            </div>
            <div class="form-sor">
                <label>Cél</label>
                <select id="kalkCel">
                    <option value="szintentartas">Súlyszinten tartás</option>
                    <option value="fogyas">Fogyás</option>
                    <option value="tomegnoveles">Tömegnövelés</option>
                </select>
            </div>
            <button type="button" id="kalkSzamit">Számítás</button>
            <p id="kalkEredmeny" class="kalk-eredmeny"></p>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($profilUser): ?>
<div id="naptarEdzesPopup" class="popup-overlay naptar-popup">
    <div class="popup-naptar-edzes">
        <button type="button" class="popup-close" aria-label="Bezárás">×</button>
        <h3 id="naptarPopupCim">Edzések</h3>
        <div id="naptarPopupTartalom"></div>
    </div>
</div>
<?php endif; ?>

<?php if (!$sajatProfil && $bejelentkezve): ?>
<script src="../js/profil.js" defer></script>
<?php endif; ?>
<?php if ($sajatProfil && $bejelentkezve): ?>
<script src="../js/profil_sajat.js" defer></script>
<?php endif; ?>
<?php if ($profilUser): ?>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const napok = document.querySelectorAll(".naptar-nap[data-klikkelheto='1']");
    const popup = document.getElementById("naptarEdzesPopup");
    const popupCim = document.getElementById("naptarPopupCim");
    const popupTartalom = document.getElementById("naptarPopupTartalom");
    const closeBtn = popup?.querySelector(".popup-close");
    const urlParams = new URLSearchParams(window.location.search);
    const userIdParam = urlParams.get("user_id") || "";
    const tervParam = urlParams.get("terv") || "";

    napok.forEach((nap) => {
        nap.style.cursor = "pointer";
        nap.addEventListener("click", async () => {
            const datum = nap.getAttribute("data-datum");
            if (!datum || !popup || !popupTartalom) return;
            let apiUrl = "profil_nap_edzesek.php?datum=" + encodeURIComponent(datum);
            if (userIdParam) apiUrl += "&user_id=" + encodeURIComponent(userIdParam);
            if (tervParam) apiUrl += "&terv=" + encodeURIComponent(tervParam);
            popupTartalom.innerHTML = "<p>Betöltés...</p>";
            popupCim.textContent = "Edzések – " + datum;
            popup.classList.add("open");
            try {
                const res = await fetch(apiUrl);
                const data = await res.json();
                if (data.siker && Array.isArray(data.edzesek)) {
                    if (data.edzesek.length === 0) {
                        popupTartalom.innerHTML = "<p class='ures-hint'>Nincs edzés ezen a napon.</p>";
                    } else {
                        popupTartalom.innerHTML = "<ul class='naptar-popup-lista'>" +
                            data.edzesek.map(e => "<li><a href='edzes_reszletek.php?id=" + e.id + "'>" +
                                "<span class='edzes-nev'>" + (e.nev || "").replace(/</g, "&lt;") + "</span>" +
                                "<span class='edzes-meta'>" + (e.datum || "") + " • " + (e.idotartam ? (Math.floor(e.idotartam/3600) + ":" + String(Math.floor((e.idotartam%3600)/60)).padStart(2,"0") + ":" + String(e.idotartam%60).padStart(2,"0")) : "0:00:00") + " • " + (e.osszsuly || 0) + " kg</span>" +
                                "</a></li>").join("") + "</ul>";
                    }
                } else {
                    popupTartalom.innerHTML = "<p class='ures-hint'>Hiba a betöltésnél.</p>";
                }
            } catch (e) {
                popupTartalom.innerHTML = "<p class='ures-hint'>Nem sikerült betölteni.</p>";
            }
        });
    });

    if (closeBtn && popup) {
        closeBtn.addEventListener("click", () => popup.classList.remove("open"));
    }
    if (popup) {
        popup.addEventListener("click", (e) => { if (e.target === popup) popup.classList.remove("open"); });
        document.addEventListener("keydown", (e) => { if (e.key === "Escape" && popup.classList.contains("open")) popup.classList.remove("open"); });
    }
});
</script>
<?php endif; ?>
</body>
</html>
