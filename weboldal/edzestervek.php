<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login-html.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

$tervek = [];

// Ha a tábla még nem létezik, egyszerűen üres listát mutatunk
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
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <title>Edzéstervek</title>
</head>
<body class="fooldal-body">
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

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <h1>Mentett edzéstervek</h1>

                <?php if (empty($tervek)): ?>
                    <p class="posts-placeholder">
                        Még nincs elmentett edzésterved. Hozz létre egyet az „Új edzés” fülön, majd kattints az „Edzés mentése” gombra.
                    </p>
                <?php else: ?>
                    <?php foreach ($tervek as $terv): ?>
                        <article class="terv-kartya">
                            <h2><?php echo htmlspecialchars($terv["nev"]); ?></h2>
                            <p class="terv-datum">
                                Létrehozva: <?php echo htmlspecialchars($terv["letrehozva"]); ?>
                            </p>
                            <?php
                                $sorok = json_decode($terv["tartalom"], true) ?: [];
                            ?>
                            <?php if (!empty($sorok)): ?>
                                <ul class="terv-gyakorlatok">
                                    <?php foreach ($sorok as $sor): ?>
                                        <li>
                                            <?php echo htmlspecialchars($sor["nev"] ?? ""); ?>
                                            <?php
                                                $set  = isset($sor["set"]) ? (int)$sor["set"] : 0;
                                                $rep  = isset($sor["rep"]) ? (int)$sor["rep"] : 0;
                                                $suly = isset($sor["suly"]) ? (int)$sor["suly"] : 0;
                                                $reszletek = [];
                                                if ($set > 0)  $reszletek[] = $set . "x";
                                                if ($rep > 0)  $reszletek[] = $rep . " ismétlés";
                                                if ($suly > 0) $reszletek[] = $suly . " kg";
                                                if (!empty($reszletek)) {
                                                    echo " – " . htmlspecialchars(implode(", ", $reszletek));
                                                }
                                            ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nincsenek gyakorlatok mentve ehhez a tervhez.</p>
                            <?php endif; ?>
                            <a href="ujedzes.php?terv_id=<?php echo (int)$terv["id"]; ?>" class="terv-inditas-gomb">Terv indítása</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="friends-box">
                <h2>Megjegyzés</h2>
                <p class="friends-info">
                    Itt látod felsorolva az összes elmentett edzéstervedet.
                    Új tervet az „Új edzés” menüpont alatt tudsz készíteni.
                </p>
            </aside>
        </div>
    </main>
</body>
</html>

