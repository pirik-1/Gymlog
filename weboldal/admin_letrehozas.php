<?php
/**
 * Admin felhasználó létrehozása – futtasd egyszer, majd töröld vagy ne futtasd újra.
 */
require "db.php";

$adminEmail = "admin@gymlog.hu";
$adminJelszo = "Admin123!";  // váltsd meg a bejelentkezés után!
$adminNev = "Admin";

$check = $conn->prepare("SELECT id FROM felhasznalo WHERE email = ?");
$check->bind_param("s", $adminEmail);
$check->execute();
$van = $check->get_result()->fetch_assoc();

if ($van) {
    die("Az admin felhasználó (admin@gymlog.hu) már létezik. Jelentkezz be ezzel a címmel.");
}

$hash = password_hash($adminJelszo, PASSWORD_DEFAULT);
$admin = 1;
$stmt = $conn->prepare("INSERT INTO felhasznalo (email, jelszo, nev, admin) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $adminEmail, $hash, $adminNev, $admin);

if ($stmt->execute()) {
    echo "Admin felhasználó létrehozva!\n\n";
    echo "Bejelentkezési adatok:\n";
    echo "  E-mail: " . $adminEmail . "\n";
    echo "  Jelszó: " . $adminJelszo . "\n\n";
    echo "Jelentkezz be a <a href='login-html.php'>Bejelentkezés</a> oldalon.\n";
    echo "A Gyakorlat beajánlás oldalon majd jóváhagyhatod a beérkező gyakorlatokat.\n";
    echo "\n\nFIGYELEM: Töröld ezt a fájlt (admin_letrehozas.php) biztonsági okokból, vagy váltsd meg a jelszót!";
} else {
    echo "Hiba az admin létrehozásakor.";
}
