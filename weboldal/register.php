<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register-html.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$nev = trim($_POST["nev"] ?? "");
$jelszo1 = $_POST["jelszo"] ?? "";
$jelszo2 = $_POST["jelszo_ujra"] ?? "";

// Alap ellenőrzések
if ($email === "" || $nev === "" || $jelszo1 === "" || $jelszo2 === "") {
    $_SESSION["hiba"] = "Minden mező kitöltése kötelező.";
    header("Location: register-html.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["hiba"] = "Érvénytelen e-mail cím.";
    header("Location: register-html.php");
    exit;
}

if ($jelszo1 !== $jelszo2) {
    $_SESSION["hiba"] = "A két jelszó nem egyezik.";
    header("Location: register-html.php");
    exit;
}

// Jelszó szabályok: min 8, max 64 karakter, legalább 1 szám, legalább 1 betű
if (strlen($jelszo1) < 8) {
    $_SESSION["hiba"] = "A jelszónak legalább 8 karakter hosszúnak kell lennie.";
    header("Location: register-html.php");
    exit;
}
if (strlen($jelszo1) > 64) {
    $_SESSION["hiba"] = "A jelszó legfeljebb 64 karakter hosszú lehet.";
    header("Location: register-html.php");
    exit;
}
if (!preg_match('/[0-9]/', $jelszo1)) {
    $_SESSION["hiba"] = "A jelszónak legalább egy számot kell tartalmaznia.";
    header("Location: register-html.php");
    exit;
}
if (!preg_match('/[a-zA-Z]/', $jelszo1)) {
    $_SESSION["hiba"] = "A jelszónak legalább egy betűt kell tartalmaznia.";
    header("Location: register-html.php");
    exit;
}

// Felhasználónév és email foglaltság ellenőrzése
$check = $conn->prepare("SELECT id FROM felhasznalo WHERE email = ? OR nev = ?");
$check->bind_param("ss", $email, $nev);
$check->execute();
$exists = $check->get_result()->fetch_assoc();

if ($exists) {
    $emailCheck = $conn->prepare("SELECT id FROM felhasznalo WHERE email = ?");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    if ($emailCheck->get_result()->fetch_assoc()) {
        $_SESSION["hiba"] = "Ezzel az e-mail címmel már létezik fiók.";
    } else {
        $_SESSION["hiba"] = "Ezzel a felhasználónévvel már létezik fiók.";
    }
    header("Location: register-html.php");
    exit;
}

// Mentés
$hash = password_hash($jelszo1, PASSWORD_DEFAULT);
$admin = 0;

$sql = "INSERT INTO felhasznalo (email, jelszo, nev, admin) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $email, $hash, $nev, $admin);

if ($stmt->execute()) {
    // siker: vissza loginra
    header("Location: login-html.php");
    exit;
}

$_SESSION["hiba"] = "Hiba a regisztráció során.";
header("Location: register-html.php");
exit;
