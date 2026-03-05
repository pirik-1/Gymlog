<?php
session_start();
require "db.php";
require "functions.php";

$edzesId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$edzes = null;

if ($edzesId > 0) {
    $stmt = $conn->prepare("SELECT e.id, e.nev, e.idotartam, e.osszsuly, e.datum, e.leiras, e.felhasznaloId, f.nev as felhasznaloNev 
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
$posztId = null;
$posztKommentek = [];
$isAdmin = isset($_SESSION["user_id"]) && ($_SESSION["role"] ?? "") === "admin";
$lekPoszt = $conn->prepare("SELECT id FROM poszt WHERE edzesId = ?");
$lekPoszt->bind_param("i", $edzesId);
$lekPoszt->execute();
$posztSor = $lekPoszt->get_result()->fetch_assoc();
if ($posztSor) {
    $posztId = (int)$posztSor["id"];
    $posztKommentek = kommentekLekeres($conn, [$posztId]);
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
    <script>window.gymlogAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;</script>
    <title>Edzés részletei</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <article class="edzes-reszlet-kartya">
                    <h1 class="edzes-reszlet-cim"><?php echo htmlspecialchars($edzes["nev"]); ?></h1>
                    <div class="edzes-reszlet-meta">
                        <span class="meta-cimke"><a href="profil.php?user_id=<?php echo (int)$edzes["felhasznaloId"]; ?>" class="meta-profil-link"><?php echo htmlspecialchars($edzes["felhasznaloNev"]); ?></a></span>
                        <span class="meta-cimke"><?php echo htmlspecialchars($edzes["datum"]); ?></span>
                        <span class="meta-cimke">Időtartam: <?php echo gmdate("H:i:s", (int)$edzes["idotartam"]); ?></span>
                        <span class="meta-cimke">Összsúly: <?php echo (int)$edzes["osszsuly"]; ?> kg</span>
                    </div>

                    <h2 class="edzes-reszlet-alcim">Gyakorlatok</h2>
                    <?php if (empty($sorok)): ?>
                        <p>Nincs gyakorlat rögzítve.</p>
                    <?php else: ?>
                        <div class="edzes-gyakorlat-lista">
                            <?php foreach ($sorok as $sor): 
                                $gyakNev = $sor["nev"] ?? "";
                                $szettek = $sor["szettek"] ?? [];
                                if (empty($szettek) && isset($sor["set"])) {
                                    $szettek = array_fill(0, (int)$sor["set"], ["rep" => $sor["rep"] ?? 0, "suly" => $sor["suly"] ?? 0, "kesz" => false]);
                                }
                            ?>
                            <div class="edzes-gyakorlat-blokk">
                                <h3 class="gyakorlat-nev-reszlet"><?php echo htmlspecialchars($gyakNev); ?></h3>
                                <ul class="szett-lista-reszlet">
                                    <?php foreach ($szettek as $idx => $sz): 
                                        $rep = (int)($sz["rep"] ?? 0);
                                        $suly = (int)($sz["suly"] ?? 0);
                                        $kesz = !empty($sz["kesz"]);
                                        $szoveg = $rep > 0 ? $rep . " ismétlés" : "";
                                        if ($suly > 0) $szoveg .= ($szoveg ? ", " : "") . $suly . " kg";
                                        $szoveg = $szoveg ?: "—";
                                    ?>
                                    <li class="szett-sor-reszlet <?php echo $kesz ? "kesz" : ""; ?>">
                                        <span class="szett-szam"><?php echo ($idx + 1); ?>.</span>
                                        <?php if ($kesz): ?><span class="szett-pipa">✓</span><?php endif; ?>
                                        <span class="szett-adatok"><?php echo htmlspecialchars($szoveg); ?></span>
                                        <?php if ($kesz): ?><span class="szett-status">Befejezve</span><?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION["user_id"]) && !empty($sorok)): ?>
                        <button type="button" id="mentesTervkent" class="mentes-tervkent-gomb">Mentsd saját edzéstervként</button>
                        <p id="mentesUzenet"></p>
                    <?php endif; ?>
                </article>
                <p><a href="index.php" class="vissza-link">Vissza a bejegyzésekhez</a></p>
            </section>
            <aside class="friends-box">
                <h2>Kommentek</h2>
                <?php if ($posztId === null): ?>
                    <p class="friends-info">Ehhez a bejegyzéshez nincs komment szekció.</p>
                <?php else: ?>
                    <div class="poszt-kommentek">
                        <ul class="komment-lista">
                            <?php foreach ($posztKommentek as $k): ?>
                                <li class="komment-elem" data-komment-id="<?php echo (int)$k["id"]; ?>">
                                    <span class="komment-szerzo"><?php echo htmlspecialchars($k["felhasznaloNev"]); ?></span>
                                    <span class="komment-datum"><?php echo htmlspecialchars($k["datum"]); ?></span>
                                    <?php if ($isAdmin): ?>
                                        <button type="button" class="komment-torles-gomb" title="Törlés">✕</button>
                                    <?php endif; ?>
                                    <span class="komment-tartalom"><?php echo htmlspecialchars($k["tartalom"]); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($_SESSION["user_id"])): ?>
                            <form class="komment-uj-form" data-poszt-id="<?php echo $posztId; ?>">
                                <input type="text" name="tartalom" placeholder="Írj kommentet..." maxlength="500" required>
                                <button type="submit">Küldés</button>
                            </form>
                        <?php else: ?>
                            <p class="friends-info">Jelentkezz be a kommenteléshez.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
