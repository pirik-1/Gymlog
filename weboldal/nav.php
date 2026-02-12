<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$bejelentkezve = isset($_SESSION["user_id"]);
$role = $bejelentkezve ? ($_SESSION["role"] ?? "felhasznalo") : "vendeg";
$roleSzoveg = [
    "vendeg" => "Szerep: Vendég",
    "felhasznalo" => "Szerep: Felhasználó",
    "admin" => "Szerep: Rendszergazda"
][$role];
?>
<ul>
    <li><a class="home-btn-a" href="index.php"><img class="home-btn" src="../img/gymlog-white-removebg.png"></a></li>
    <li><a href="index.php">Főoldal</a></li>
    <li><a href="ujedzes.php">Új edzés</a></li>
    <li><a href="edzestervek.php">Edzéstervek</a></li>
    <li><a href="kozosseg.php">Közösség</a></li>
    <li><a href="profil.php">Profil</a></li>
    <li class="nav-spacer"></li>
    <li class="nav-role"><?php echo htmlspecialchars($roleSzoveg); ?></li>
    <?php if ($bejelentkezve): ?>
        <li><a href="logout.php">Kijelentkezés</a></li>
    <?php else: ?>
        <li><a href="login-html.php">Bejelentkezés</a></li>
    <?php endif; ?>
</ul>
