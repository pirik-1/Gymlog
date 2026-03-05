<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/regisztracio.js" defer></script>
    <title>Regisztráció</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="auth-main">
        <div class="auth-card">
            <h1>Regisztráció</h1>

            <?php 
            $hibaUzenet = $_SESSION["hiba"] ?? "";
            if ($hibaUzenet) unset($_SESSION["hiba"]);
            ?>
            <div id="regHiba" class="auth-error"<?php echo $hibaUzenet ? '' : ' style="display:none"'; ?>><?php echo $hibaUzenet ? htmlspecialchars($hibaUzenet) : ''; ?></div>

            <form action="register.php" method="post" class="auth-form" id="regForm">
                <label>
                    Felhasználónév
                    <input type="text" name="nev" id="nev" placeholder="Felhasználónév" required>
                </label>
                <label>
                    E-mail
                    <input type="email" name="email" id="email" placeholder="E-mail" required>
                </label>
                <label>
                    Jelszó
                    <input type="password" name="jelszo" id="jelszoReg" placeholder="Jelszó" required minlength="8" maxlength="64" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,64}$" title="Legalább 8 karakter, szám és betű">
                    <span class="auth-hint">Min. 8 karakter, legalább 1 szám és 1 betű</span>
                </label>
                <label>
                    Jelszó újra
                    <input type="password" name="jelszo_ujra" id="jelszoRegUjra" placeholder="Jelszó újra" required minlength="8" maxlength="64">
                </label>
                <span class="mutasdajelszot" id="mutasdReg">Mutasd a jelszót</span>
                <div class="gombSor">
                    <button type="submit">Regisztráció</button>
                    <a href="login-html.php" class="gomb">Vissza</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
