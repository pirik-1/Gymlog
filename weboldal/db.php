<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "gymlog";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error . "<br>Ellenőrizd, hogy az XAMPP MySQL szolgáltatása fut-e!");
}

?>