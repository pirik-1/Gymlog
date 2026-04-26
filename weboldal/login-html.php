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
    <script src="../js/bejelentkezes.js" defer></script>
    <title>Bejelentkezés</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="auth-main">
        <div class="auth-card">
            <h1>Bejelentkezés</h1>

            <?php if (!empty($_SESSION["hiba"])): ?>
                <p class="auth-error">
                    <?php
                        echo htmlspecialchars($_SESSION["hiba"]);
                        unset($_SESSION["hiba"]);
                    ?>
                </p>
            <?php endif; ?>

            <form action="login.php" method="post" class="auth-form">
                <label>
                    E-mail
                    <input type="email" name="email" id="email" placeholder="E-mail" required>
                </label>
                <label>
                    Jelszó
                    <input type="password" name="jelszo" id="jelszo" placeholder="Jelszó" required>
                </label>
                <span class="mutasdajelszot" id="mutasd">Mutasd a jelszót</span>
                <div class="gombSor">
                    <button type="submit">Bejelentkezés</button>
                    <a href="register-html.php" class="gomb">Regisztráció</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
