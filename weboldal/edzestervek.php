<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;
$tervek = $bejelentkezve ? getTervek($conn, $userId) : [];
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
    <title>Edzéstervek</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <h1>Mentett edzéstervek</h1>

                <?php if (!$bejelentkezve): ?>
                    <p class="posts-placeholder">
                        Jelentkezz be az edzéstervek megtekintéséhez és mentéséhez.
                        <br>
                        <br><a href="login-html.php" class="auth-link-inline" style="color: rgb(60, 75, 33); background: rgba(0, 0, 0, 0.15); border-radius: 16px; padding:10px;">Bejelentkezés</a>
                    </p>
                <?php elseif (empty($tervek)): ?>
                    <p class="posts-placeholder">
                        Még nincs elmentett edzésterved. Hozz létre egyet az „Új edzés” fülön, majd kattints az „Edzés mentése” gombra.
                    </p>
                    <p><a href="index.php" class="vissza-link">Vissza a főoldalra</a></p>
                <?php else: ?>
                    <?php foreach ($tervek as $terv): 
                        $sorok = json_decode($terv["tartalom"], true) ?: [];
                    ?>
                        <article class="edzes-reszlet-kartya terv-kartya">
                            <h1 class="edzes-reszlet-cim"><?php echo htmlspecialchars($terv["nev"]); ?></h1>
                            <div class="edzes-reszlet-meta">
                                <span class="meta-cimke">Létrehozva: <?php echo htmlspecialchars($terv["letrehozva"]); ?></span>
                            </div>
                            <h2 class="edzes-reszlet-alcim">Gyakorlatok</h2>
                            <?php if (empty($sorok)): ?>
                                <p>Nincsenek gyakorlatok mentve ehhez a tervhez.</p>
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
                                                $szoveg = $rep > 0 ? $rep . " ismétlés" : "";
                                                if ($suly > 0) $szoveg .= ($szoveg ? ", " : "") . $suly . " kg";
                                                $szoveg = $szoveg ?: "—";
                                            ?>
                                            <li class="szett-sor-reszlet">
                                                <span class="szett-szam"><?php echo ($idx + 1); ?>.</span>
                                                <span class="szett-adatok"><?php echo htmlspecialchars($szoveg); ?></span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="terv-kartya-gombok">
                                <a href="ujedzes.php?terv_id=<?php echo (int)$terv["id"]; ?>" class="terv-inditas-gomb">Terv indítása</a>
                                <button type="button" class="terv-torles-gomb" data-terv-id="<?php echo (int)$terv["id"]; ?>" title="Terv törlése">Törlés</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <p><a href="index.php" class="vissza-link">Vissza a főoldalra</a></p>
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

    <?php if ($bejelentkezve && !empty($tervek)): ?>
    <script>
        document.querySelectorAll(".terv-torles-gomb").forEach(btn => {
            btn.addEventListener("click", async function() {
                if (!confirm("Biztosan törlöd ezt az edzéstervet?")) return;
                const tervId = this.getAttribute("data-terv-id");
                const kartya = this.closest(".terv-kartya");
                try {
                    const res = await fetch("terv_torles.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ terv_id: parseInt(tervId, 10) })
                    });
                    const data = await res.json();
                    if (data.siker) {
                        kartya.remove();
                        if (!document.querySelector(".terv-kartya")) {
                            location.reload();
                        }
                    } else {
                        alert(data.uzenet || "Hiba történt.");
                    }
                } catch (e) {
                    alert("Nem sikerült kapcsolódni a szerverhez.");
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>

