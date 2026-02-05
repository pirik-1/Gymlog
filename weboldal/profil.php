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

<!-- PROFIL -->
<div class="profil-container">

    <!-- ADATOK -->
    <div class="profil-card profil-adatok">
        <h2>Adatok</h2>
        <p><b>Név:</b></p>
        <p><b>E-mail:</b></p>
        <p><b>Regisztráció:</b></p>
    </div>

    <!-- STATISZTIKÁK -->
    <div class="profil-card">
        <h2>Statisztikák</h2>
        <div class="stat-grid">
            <div class="stat-box">
                <h3>128</h3>
                <p>Edzés</p>
            </div>
            <div class="stat-box">
                <h3>412</h3>
                <p>Óra</p>
            </div>
            <div class="stat-box">
                <h3>32</h3>
                <p>Barát</p>
            </div>
        </div>
    </div>

    <!-- BARÁTOK -->
    <div class="profil-card profil-full">
        <h2>Barátok</h2>

        <div class="barat">
            <span></span>
            <button>Profil</button>
        </div>

        <div class="barat">
            <span></span>
            <button>Profil</button>
        </div>

        <div class="barat">
            <span></span>
            <button>Profil</button>
        </div>
    </div>

    <div class="kaloria-card">
        <h2>Kalória kalkulátor</h2>
        <p><b>Név:</b></p>
        <p><b>E-mail:</b></p>
        <p><b>Regisztráció:</b></p>
    </div>

</div>

</body>
</html>
