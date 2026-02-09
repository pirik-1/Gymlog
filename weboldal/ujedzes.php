<?php
session_start();
require "db.php";

$tervAdatok = null;
if (isset($_GET["terv_id"]) && is_numeric($_GET["terv_id"])) {
    $tervId = (int)$_GET["terv_id"];
    $userId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : 0;
    
    if ($userId > 0) {
        $check = $conn->query("SHOW TABLES LIKE 'edzesterv_mentes'");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("SELECT nev, tartalom FROM edzesterv_mentes WHERE id = ? AND felhasznaloId = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $tervId, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $tervAdatok = [
                        "nev" => $row["nev"],
                        "tartalom" => json_decode($row["tartalom"], true) ?: []
                    ];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
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
    
    <title>Új edzés</title>
</head>
<body class="fooldal-body">
    <style>
        body{    
            background-image: url(../img/moonlit-asteroid.jpg);
            background-attachment: fixed;  
            background-size: cover;
            color: white;
        }
    </style>
    <ul>
        <li><a class="home-btn-a" href="index.php"><img class="home-btn" src="../img/gymlog-white-removebg.png"></a></li>
        <li><a href="index.php">Főoldal</a></li>
        <li><a href="ujedzes.php">Új edzés</a></li>
        <li><a href="edzestervek.php">Edzéstervek</a></li>
        <li><a href="kozosseg.php">Közösség</a></li>
        <li><a href="statisztikak.php">Statisztikák</a></li>
        <li><a href="profil.php">Profil</a></li>

        <li class="nav-spacer"></li>

        <li class="nav-role">Szerep: Felhasználó</li>
        <li><a href="login-html.php">Kijelentkezés</a></li>
    </ul>
    <!-- <h1 style="text-align: center;">Új edzés<br> gombra kattintottál!</h1> -->
    
    <div class="loginDiv">
        <h1>Új edzés</h1>

        <p class="leiras">Adj nevet az edzésnek, válassz gyakorlatokat a jobb oldali listából, és állítsd be a szetteket / ismétléseket.</p>

        <p class="mezocim">Edzés neve</p>
        <input type="text" id="edzesNev" class="inputok" placeholder="pl. Felsőtest edzés">

        <div class="edzes-meta">
            <span id="gyakorlatCount">0 gyakorlat</span>
        </div>

        <div id="valasztottGyakorlatok" class="gyakorlat-lista">
            <p class="ures-info">Még nem adtál hozzá gyakorlatot.</p>
        </div>

        <button type="button" id="ujGyakorlatGomb">Gyakorlat hozzáadása</button>

        <button type="button" id="mentes" class="mentes-gomb">Edzés mentése</button>

        <p id="hiba"></p>
    </div>

    <div class="gyakorlat-panel" id="gyakorlatPanel">
        <div class="gyakorlat-panel-fejlec">
            <h2>Gyakorlat választó</h2>
            <button type="button" id="panelZar" class="panel-zar">✕</button>
        </div>

        <input type="text" id="gyakorlatKereses" class="gyakorlat-kereses" placeholder="Keresés a gyakorlatok között...">

        <div class="gyakorlat-panel-lista" id="gyakorlatListaOldal">
            <button type="button" class="gyakorlat-item" data-nev="Fekvenyomás">Fekvenyomás (mell)</button>
            <button type="button" class="gyakorlat-item" data-nev="Guggolás">Guggolás (láb)</button>
            <button type="button" class="gyakorlat-item" data-nev="Felhúzás">Felhúzás (hát)</button>
            <button type="button" class="gyakorlat-item" data-nev="Vállból nyomás">Vállból nyomás (váll)</button>
            <button type="button" class="gyakorlat-item" data-nev="Bicepsz hajlítás">Bicepsz hajlítás</button>
            <button type="button" class="gyakorlat-item" data-nev="Tricepsz letolás">Tricepsz letolás</button>
        </div>
    </div>
</body>
</html>