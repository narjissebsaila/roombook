<?php
/*
    Fichier : actions/user_store.php
    Rôle    : créer un nouvel utilisateur (admin uniquement).
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/utilisateurs.php");
    exit;
}

$nom          = trim($_POST["nom"] ?? "");
$email        = trim($_POST["email"] ?? "");
$mot_de_passe = $_POST["mot_de_passe"] ?? "";
$role         = $_POST["role"] ?? "client";
$type_client  = $_POST["type_client"] ?? "autre";
$departement  = trim($_POST["departement"] ?? "");
$telephone    = trim($_POST["telephone"] ?? "");

if ($nom === "" || $email === "" || $mot_de_passe === "") {
    header("Location: ../pages/user_create.php?error=vide");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/user_create.php?error=email");
    exit;
}

// Vérifier que l'email n'existe pas
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header("Location: ../pages/user_create.php?error=email");
    exit;
}

// Whitelist
if (!in_array($role, ["admin", "client"]))                $role = "client";
if (!in_array($type_client, ["prof", "etudiant", "autre"])) $type_client = "autre";
if ($role === "admin")                                    $type_client = "autre";

$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

$sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, type_client, departement, telephone)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nom, $email, $hash, $role, $type_client, $departement, $telephone]);

header("Location: ../pages/utilisateurs.php?success=ajout");
exit;
