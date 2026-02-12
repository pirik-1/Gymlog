<?php
session_start();
require "db.php";
require "functions.php";

$gyakorlatok = getOsszesGyakorlat($conn);
$tervAdatok = null;
if (isset($_GET["terv_id"]) && is_numeric($_GET["terv_id"])) {
    $tervId = (int)$_GET["terv_id"];
    $userId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : 0;
    if ($userId > 0) {
        $tervAdatok = getTervAdatok($conn, $tervId, $userId);
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/ujedzes.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <script src="../js/ujedzes.js" defer></script>
    <?php if ($tervAdatok): ?>
    <script>
        window.tervAdatok = <?php echo json_encode($tervAdatok, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <?php endif; ?>
    <script>
        window.vendeg = <?php echo isset($_SESSION["user_id"]) ? "false" : "true"; ?>;
    </script>
    <title>Új edzés</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>
    
    <div class="loginDiv">
        <h1>Új edzés</h1>

        <p class="leiras">Adj nevet az edzésnek, válassz gyakorlatokat a jobb oldali listából, és állítsd be a szetteket / ismétléseket.</p>

        <p class="mezocim">Edzés neve</p>
        <input type="text" id="edzesNev" class="inputok" placeholder="pl. Felsőtest edzés">

        <div class="edzes-meta">
            <span id="gyakorlatCount">0 gyakorlat</span>
            <span id="idotartamKijelzo" class="idotartam-kijelzo">00:00</span>
        </div>

        <div class="edzes-vezerlo">
            <button type="button" id="inditGomb" class="indit-gomb">Indít</button>
            <button type="button" id="befejezGomb" class="befejez-gomb" disabled>Befejez</button>
        </div>

        <div id="valasztottGyakorlatok" class="gyakorlat-lista">
            <p class="ures-info">Még nem adtál hozzá gyakorlatot.</p>
        </div>

        <button type="button" id="ujGyakorlatGomb">Gyakorlat hozzáadása</button>

        <button type="button" id="mentes" class="mentes-gomb">Edzés mentése (tervként)</button>
        <?php if (!isset($_SESSION["user_id"])): ?>
        <p class="vendeg-hint">A mentéshez <a href="login-html.php">jelentkezz be</a>.</p>
        <?php endif; ?>
        <p id="hiba"></p>
    </div>

    <div class="gyakorlat-panel" id="gyakorlatPanel">
        <div class="gyakorlat-panel-fejlec">
            <h2>Gyakorlat választó</h2>
            <button type="button" id="panelZar" class="panel-zar">✕</button>
        </div>

        <input type="text" id="gyakorlatKereses" class="gyakorlat-kereses" placeholder="Keresés a gyakorlatok között...">

        <div class="gyakorlat-panel-lista" id="gyakorlatListaOldal">
            <?php foreach ($gyakorlatok as $g): ?>
            <button type="button" class="gyakorlat-item" data-nev="<?php echo htmlspecialchars($g); ?>"><?php echo htmlspecialchars($g); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>