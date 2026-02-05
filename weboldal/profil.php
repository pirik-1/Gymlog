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

<!-- NAVBAR -->
<ul>
    <li><a class="home-btn-a" href="index.php"><img class="home-btn" src="../img/gymlog-white-removebg.png"></a></li>
    <li><a href="index.php">Főoldal</a></li>
    <li><a href="ujedzes.php">Új edzés</a></li>
    <li><a href="kozosseg.php">Közösség</a></li>
    <li><a href="statisztikak.php">Statisztikák</a></li>
    <li><a href="profil.php">Profil</a></li>

    <li class="nav-spacer"></li>

    <li class="nav-role">Szerep: Felhasználó</li>
    <li><a href="login-html.php">Kijelentkezés</a></li>
</ul>

<main class="profil-main">
    <div class="profil-shell">

        <section class="profil-left">
            <div class="profil-card profil-basic">
                <h1>Profil</h1>
                <p><span class="label">Név:</span> <span class="value">Minta Felhasználó</span></p>
                <p><span class="label">E‑mail:</span> <span class="value">felhasznalo@example.com</span></p>
                <p><span class="label">Regisztráció:</span> <span class="value">2025‑01‑01</span></p>
            </div>

            <div class="profil-card profil-stats">
                <h2>Statisztikák</h2>
                <div class="profil-stat-grid">
                    <div class="profil-stat-box">
                        <div class="number">128</div>
                        <div class="label">edzés</div>
                    </div>
                    <div class="profil-stat-box">
                        <div class="number">412</div>
                        <div class="label">óra</div>
                    </div>
                    <div class="profil-stat-box">
                        <div class="number">32</div>
                        <div class="label">barát</div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="profil-right">
            <div class="profil-card profil-friends">
                <h2>Barátok</h2>
                <ul class="friends-list">
                    <!-- Ide kerülnek majd dinamikusan a barátok -->
                </ul>
                <p class="friends-hint">Itt jelennek meg azok, akiket barátnak jelölsz.</p>
            </div>
        </aside>

    </div>
</main>

</body>
</html>
