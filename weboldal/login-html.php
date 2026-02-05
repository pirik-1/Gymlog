<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <script src="../js/bejelentkezes.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <title>Bejelentkezés</title>
</head>
<body>

<form class="loginDiv" method="POST" action="login.php">
    <h1>GymLog</h1>

    <h2>E-mail</h2>
    <input type="email" name="email" id="email" class="inputok" placeholder="E-mail" required>

    <h2>Jelszó:</h2>
    <input type="password" name="jelszo" id="jelszo" class="inputok" placeholder="Jelszó" required>

    <br>
    <span class="mutasdajelszot" id="mutasd">Mutasd a jelszót</span>
    <br><br>

    <div class="gombSor">
    <button type="submit">Bejelentkezés</button>
    <a href="register-html.php" class="gomb">Regisztráció</a>
    </div>


    <br><br>
    <p id="hiba" style="color:red;">
        <?php
        if (isset($_SESSION["hiba"])) {
            echo htmlspecialchars($_SESSION["hiba"]);
            unset($_SESSION["hiba"]);
        }
        ?>
    </p>
</form>

</body>
</html>
