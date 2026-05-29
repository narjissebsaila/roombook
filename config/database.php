<?php
/*
    Fichier : config/database.php
    Rôle    : créer la connexion PDO à la base de données.
    À adapter selon votre environnement (XAMPP, WAMP, MAMP, ...).
*/

$host    = "localhost";
$port    = "3306";        // mettez 3307 si vous utilisez un port alternatif
$dbname  = "roombook";
$user    = "root";
$pass    = "1234";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
