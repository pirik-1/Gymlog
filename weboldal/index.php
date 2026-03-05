<?php
session_start();
require "db.php";
require "functions.php";
$csakBaratok = false;
if (isset($_SESSION["user_id"]) && isset($_GET["szures"]) && $_GET["szures"] === "baratok") {
    $csakBaratok = true;
}
$posztok = getPosztok($conn, 50, isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null, $csakBaratok);
$baratok = [];
if (isset($_SESSION["user_id"])) {
    $baratok = getBaratok($conn, (int)$_SESSION["user_id"]);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>

    <title>Bejelentkezés</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <h1>Bejegyzések</h1>
                <?php if (isset($_SESSION["user_id"])): ?>
                <div class="posts-szuro">
                    <span>Mutasd:</span>
                    <a href="index.php" class="szuro-gomb <?php echo !$csakBaratok ? 'szuro-aktiv' : ''; ?>">Mindenki</a>
                    <a href="index.php?szures=baratok" class="szuro-gomb <?php echo $csakBaratok ? 'szuro-aktiv' : ''; ?>">Csak barátok</a>
                </div>
                <?php endif; ?>
                <?php if (empty($posztok)): ?>
                    <p class="posts-placeholder">
                        Itt fognak megjelenni a barátaid és a saját posztjaid. Befejezz egy edzést az „Új edzés” oldalon, és megjelenik itt!
                    </p>
                <?php else: ?>
                    <?php foreach ($posztok as $poszt): ?>
                        <article class="poszt-kartya <?php echo !empty($poszt["edzesId"]) ? 'poszt-kattinthato' : ''; ?>">
                            <?php 
                                $suffix = " befejezett egy edzést: ";
                                $tartalom = $poszt["tartalom"];
                                $pos = strpos($tartalom, $suffix);
                                $megjelenit = ($pos !== false && !empty($poszt["felhasznaloNev"])) 
                                    ? htmlspecialchars($poszt["felhasznaloNev"]) . htmlspecialchars(substr($tartalom, $pos)) 
                                    : htmlspecialchars($tartalom);
                            ?>
                            <?php if (!empty($poszt["edzesId"])): ?>
                                <a href="edzes_reszletek.php?id=<?php echo (int)$poszt["edzesId"]; ?>" class="poszt-link">
                                    <p class="poszt-tartalom"><?php echo $megjelenit; ?></p>
                                    <p class="poszt-datum"><?php echo htmlspecialchars($poszt["datum"]); ?></p>
                                    <span class="poszt-reszlet-hint">Részletek →</span>
                                </a>
                            <?php else: ?>
                                <p class="poszt-tartalom"><?php echo $megjelenit; ?></p>
                                <p class="poszt-datum"><?php echo htmlspecialchars($poszt["datum"]); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="friends-box">
                <h2>Barátok listája</h2>
                <?php if (!isset($_SESSION["user_id"])): ?>
                    <p class="friends-info">Jelentkezz be a barátok megtekintéséhez.</p>
                <?php elseif (empty($baratok)): ?>
                    <p class="friends-info">
                        Még nincs barátod. Jelöld barátnak másokat a Közösség oldalon.
                    </p>
                <?php else: ?>
                    <ul class="friends-lista">
                        <?php foreach ($baratok as $b): ?>
                            <li><a href="profil.php?user_id=<?php echo (int)$b["id"]; ?>"><?php echo htmlspecialchars($b["nev"]); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>
        </div>
    </main>

</body>
</html>