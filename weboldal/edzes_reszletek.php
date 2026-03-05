<?php
session_start();
require "db.php";
require "functions.php";

$edzesId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$edzes = null;

if ($edzesId > 0) {
    $stmt = $conn->prepare("SELECT e.id, e.nev, e.idotartam, e.osszsuly, e.datum, e.leiras, f.nev as felhasznaloNev 
        FROM edzes e 
        JOIN felhasznalo f ON f.id = e.felhasznaloId 
        WHERE e.id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $edzesId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $edzes = $row;
        }
    }
}

if (!$edzes) {
    header("Location: index.php");
    exit;
}

$sorok = json_decode($edzes["leiras"], true) ?: [];
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
    <title>Edzés részletei</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <article class="edzes-reszlet-kartya">
                    <h1><?php echo htmlspecialchars($edzes["nev"]); ?></h1>
                    <p class="edzes-meta-reszlet">
                        <?php echo htmlspecialchars($edzes["felhasznaloNev"]); ?> • 
                        <?php echo htmlspecialchars($edzes["datum"]); ?> • 
                        Időtartam: <?php echo gmdate("H:i", (int)$edzes["idotartam"]); ?> • 
                        Összsúly: <?php echo (int)$edzes["osszsuly"]; ?> kg
                    </p>

                    <h2>Gyakorlatok</h2>
                    <?php if (empty($sorok)): ?>
                        <p>Nincs gyakorlat rögzítve.</p>
                    <?php else: ?>
                        <ul class="terv-gyakorlatok reszlet-lista">
                            <?php foreach ($sorok as $sor): ?>
                                <li>
                                    <?php 
                                        echo htmlspecialchars($sor["nev"] ?? "");
                                        echo htmlspecialchars(formatGyakorlatReszletek($sor));
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (isset($_SESSION["user_id"]) && !empty($sorok)): ?>
                        <button type="button" id="mentesTervkent" class="mentes-tervkent-gomb">Mentsd saját edzéstervként</button>
                        <p id="mentesUzenet"></p>
                    <?php endif; ?>
                </article>
                <p><a href="index.php" class="vissza-link">Vissza a bejegyzésekhez</a></p>
            </section>
            <aside class="friends-box">
                <h2>Részletek</h2>
                <p class="friends-info">
                    Itt láthatod az edzés összes gyakorlatát, szettjét és súlyát.
                    Ha tetszik, mentsd saját edzéstervként, és később indíthatod ugyanezt.
                </p>
            </aside>
        </div>
    </main>

    <?php if (isset($_SESSION["user_id"]) && !empty($sorok)): ?>
    <script>
        document.getElementById("mentesTervkent").addEventListener("click", async () => {
            const gomb = document.getElementById("mentesTervkent");
            const uzenet = document.getElementById("mentesUzenet");
            gomb.disabled = true;

            try {
                const response = await fetch("mentes_edzesterv_sablonbol.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ edzes_id: <?php echo $edzesId; ?> })
                });
                const data = await response.json();

                if (data.siker) {
                    uzenet.style.color = "red";
                    uzenet.textContent = data.uzenet || "Edzésterv sikeresen mentve!";
                } else {
                    uzenet.style.color = "red";
                    uzenet.textContent = data.uzenet || "Hiba történt.";
                    gomb.disabled = false;
                }
            } catch (e) {
                uzenet.style.color = "red";
                uzenet.textContent = "Nem sikerült kapcsolódni a szerverhez.";
                gomb.disabled = false;
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
