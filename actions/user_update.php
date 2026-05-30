<?php
/*
    Fichier : actions/user_update.php
    Rôle    : modifier un utilisateur (admin uniquement).
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/utilisateurs.php");
    exit;
}

$id           = (int)($_POST["id"] ?? 0);
$nom          = trim($_POST["nom"] ?? "");
$email        = trim($_POST["email"] ?? "");
$mot_de_passe = $_POST["mot_de_passe"] ?? "";
$role         = $_POST["role"] ?? "client";
$type_client  = $_POST["type_client"] ?? "autre";
$departement  = trim($_POST["departement"] ?? "");
$telephone    = trim($_POST["telephone"] ?? "");

if ($id <= 0 || $nom === "" || $email === "") {
    header("Location: ../pages/user_edit.php?id=$id&error=vide");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/user_edit.php?id=$id&error=email");
    exit;
}

// Vérifier que l'email n'est pas pris par un autre user
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    header("Location: ../pages/user_edit.php?id=$id&error=email");
    exit;
}

if (!in_array($role, ["admin", "client"]))                  $role = "client";
if (!in_array($type_client, ["prof", "etudiant", "autre"])) $type_client = "autre";
if ($role === "admin")                                      $type_client = "autre";

// Mise à jour (avec ou sans changement de mot de passe)
if ($mot_de_passe !== "") {
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $sql = "UPDATE utilisateurs
            SET nom = ?, email = ?, mot_de_passe = ?, role = ?, type_client = ?, departement = ?, telephone = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $hash, $role, $type_client, $departement, $telephone, $id]);
} else {
    $sql = "UPDATE utilisateurs
            SET nom = ?, email = ?, role = ?, type_client = ?, departement = ?, telephone = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $role, $type_client, $departement, $telephone, $id]);
}

header("Location: ../pages/utilisateurs.php?success=modification");
exit;
