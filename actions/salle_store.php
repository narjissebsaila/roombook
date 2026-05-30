<?php
/*
    Fichier : actions/salle_store.php
    Rôle    : créer une nouvelle salle (admin uniquement).
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/salles.php");
    exit;
}

$nom          = trim($_POST["nom"]          ?? "");
$capacite     = (int)($_POST["capacite"]    ?? 0);
$localisation = trim($_POST["localisation"] ?? "");
$type_salle   = $_POST["type_salle"]        ?? "cours";
$equipements  = trim($_POST["equipements"]  ?? "");

if ($nom === "" || $capacite <= 0) {
    header("Location: ../pages/salle_create.php?error=vide");
    exit;
}

if (!in_array($type_salle, ["cours", "tp", "reunion", "amphi"])) {
    $type_salle = "cours";
}

$sql = "INSERT INTO salles (nom, capacite, localisation, type_salle, equipements)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nom, $capacite, $localisation, $type_salle, $equipements]);

header("Location: ../pages/salles.php?success=ajout");
exit;
