<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$isAdmin = $bejelentkezve && ($_SESSION["role"] ?? "") === "admin";
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;

$sajatAjanlasok = $bejelentkezve ? getSajatGyakorlatAjanlasok($conn, $userId) : [];
$fuggoAjanlasok = $isAdmin ? getFuggoGyakorlatAjanlasok($conn) : [];
$jovahagyottAjanlasok = $isAdmin ? getJovahagyottAjanlasokLista($conn) : [];

$statusSzoveg = ["pending" => "Függőben", "approved" => "Jóváhagyva", "rejected" => "Elutasítva"];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gyakorlat beajánlás</title>
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/profiltartalom.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <style>
        .ajanlas-card { border-radius: 20px; padding: 24px; margin-bottom: 20px; }
        .ajanlas-form { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
        .ajanlas-form input { flex: 1; min-width: 180px; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.4); background: rgba(0,0,0,0.3); color: white; }
        .ajanlas-form button { padding: 10px 24px; border-radius: 12px; background: rgba(110,181,255,0.3); border: 1px solid rgba(110,181,255,0.5); color: white; cursor: pointer; }
        .ajanlas-lista { list-style: none; padding: 0; margin: 16px 0 0; }
        .ajanlas-lista li { padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .ajanlas-lista li:last-child { border-bottom: none; }
        .ajanlas-státusz { font-size: 13px; padding: 4px 10px; border-radius: 8px; }
        .ajanlas-státusz.pending { background: rgba(255,200,50,0.3); }
        .ajanlas-státusz.approved { background: rgba(80,200,80,0.3); }
        .ajanlas-státusz.rejected { background: rgba(255,80,80,0.3); }
        .admin-gombok { display: flex; gap: 8px; }
        .admin-gombok button { padding: 6px 14px; border-radius: 8px; font-size: 13px; cursor: pointer; border: none; }
        .admin-gombok .jovahagy { background: rgba(80,200,80,0.4); color: white; }
        .admin-gombok .elutasit { background: rgba(255,80,80,0.4); color: white; }
        .admin-gombok .torles { background: rgba(255,120,80,0.5); color: white; }
    </style>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

<main class="profil-main">
    <div class="profil-shell" style="grid-template-columns: 1fr;">
        <section class="profil-fo">
            <div class="profil-card ajanlas-card">
                <h1>Gyakorlat beajánlás</h1>
                <p class="leiras" style="opacity: 0.9; margin-bottom: 20px;">
                    Ajánld be a saját gyakorlataidat! Egy adminisztrátor jóváhagyja, és a listába kerülnek az „Új edzés” oldalon.
                </p>

                <?php if (!$bejelentkezve): ?>
                    <p>Jelentkezz be a beajánláshoz.</p>
                    <a href="login-html.php" class="gomb">Bejelentkezés</a>
                <?php else: ?>
                <div class="ajanlas-form">
                    <input type="text" id="ujGyakorlatNev" placeholder="Gyakorlat neve (pl. Guggolás törzselés)" maxlength="100">
                    <button type="button" id="ajanlasKuldes">Beajánlás küldése</button>
                </div>
                <p id="ajanlasUzenet" style="margin-top: 10px; font-size: 14px;"></p>

                <h2 style="margin-top: 28px; margin-bottom: 12px;">Saját beajánlásaim</h2>
                <?php if (empty($sajatAjanlasok)): ?>
                    <p class="ures-hint">Még nem ajánlottál be gyakorlatot.</p>
                <?php else: ?>
                    <ul class="ajanlas-lista">
                        <?php foreach ($sajatAjanlasok as $a): ?>
                        <li>
                            <span><strong><?php echo htmlspecialchars($a["nev"]); ?></strong> <span class="ajanlas-státusz <?php echo $a["status"]; ?>"><?php echo $statusSzoveg[$a["status"]] ?? $a["status"]; ?></span></span>
                            <span style="font-size: 13px; opacity: 0.8;"><?php echo htmlspecialchars($a["datum"]); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($isAdmin && !empty($fuggoAjanlasok)): ?>
            <div class="profil-card ajanlas-card">
                <h2>Jóváhagyásra váró beajánlások</h2>
                <ul class="ajanlas-lista">
                    <?php foreach ($fuggoAjanlasok as $a): ?>
                    <li>
                        <span><strong><?php echo htmlspecialchars($a["nev"]); ?></strong> – <?php echo htmlspecialchars($a["felhasznalo_nev"]); ?></span>
                        <div class="admin-gombok">
                            <button type="button" class="jovahagy" data-id="<?php echo (int)$a["id"]; ?>" data-status="approved">Jóváhagyás</button>
                            <button type="button" class="elutasit" data-id="<?php echo (int)$a["id"]; ?>" data-status="rejected">Elutasítás</button>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($isAdmin && !empty($jovahagyottAjanlasok)): ?>
            <div class="profil-card ajanlas-card">
                <h2>Jóváhagyott gyakorlatok</h2>
                <p class="leiras" style="opacity: 0.8; font-size: 14px; margin-bottom: 12px;">Ezek a listában szerepelnek. Törlésével kikerülnek az „Új edzés” gyakorlat választóból.</p>
                <ul class="ajanlas-lista">
                    <?php foreach ($jovahagyottAjanlasok as $a): ?>
                    <li>
                        <span><strong><?php echo htmlspecialchars($a["nev"]); ?></strong> <span style="font-size: 12px; opacity: 0.8;"><?php echo htmlspecialchars($a["felhasznalo_nev"]); ?></span></span>
                        <div class="admin-gombok">
                            <button type="button" class="torles" data-id="<?php echo (int)$a["id"]; ?>" data-status="rejected">Törlés</button>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php elseif ($isAdmin && empty($fuggoAjanlasok)): ?>
            <div class="profil-card ajanlas-card">
                <h2>Admin</h2>
                <p>Jelenleg nincs jóváhagyásra váró beajánlás.</p>
                <?php if (empty($jovahagyottAjanlasok)): ?>
                <p>Nincs még törölhető jóváhagyott gyakorlat sem.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php if ($bejelentkezve): ?>
<script>
document.getElementById("ajanlasKuldes")?.addEventListener("click", async () => {
    const input = document.getElementById("ujGyakorlatNev");
    const uzenet = document.getElementById("ajanlasUzenet");
    const nev = (input?.value || "").trim();
    if (!nev) {
        uzenet.textContent = "Adj meg egy gyakorlatnevet!";
        uzenet.style.color = "orange";
        return;
    }
    try {
        const res = await fetch("gyakorlat_ajanlas_add.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nev })
        });
        const data = await res.json();
        uzenet.textContent = data.uzenet || "";
        uzenet.style.color = data.siker ? "lightgreen" : "red";
        if (data.siker) { input.value = ""; location.reload(); }
    } catch (e) {
        uzenet.textContent = "Hiba a kapcsolatban.";
        uzenet.style.color = "red";
    }
});

document.querySelectorAll(".admin-gombok button").forEach(btn => {
    btn.addEventListener("click", async function() {
        const id = this.getAttribute("data-id");
        const status = this.getAttribute("data-status");
        if (!id || !status) return;
        try {
            const res = await fetch("gyakorlat_ajanlas_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: parseInt(id, 10), status })
            });
            const data = await res.json();
            if (data.siker) location.reload();
            else alert(data.uzenet || "Hiba.");
        } catch (e) { alert("Hiba a kapcsolatban."); }
    });
});
</script>
<?php endif; ?>
</body>
</html>
