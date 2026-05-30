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

// APRÈS
header('Content-Type: application/json'); // ✅ AJOUTER cette ligne tout en haut après les require_once

if ($nom === "" || $email === "" || $mot_de_passe === "") {
    echo json_encode(['ok'=>false,'msg'=>'Nom, email et mot de passe sont obligatoires.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok'=>false,'msg'=>'Adresse email invalide.']);
    exit;
}

// APRÈS
// ✅ REMPLACÉ : vérification doublon nom + email avec réponse JSON
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE LOWER(TRIM(nom))=LOWER(?) AND LOWER(TRIM(email))=LOWER(?)");
$stmt->execute([$nom, $email]);
if ($stmt->fetch()) {
    echo json_encode(['ok'=>false,'msg'=>'Cet utilisateur existe déjà (même nom et même email).']);
    exit;
}
$stmt2 = $pdo->prepare("SELECT id FROM utilisateurs WHERE LOWER(TRIM(email))=LOWER(?)");
$stmt2->execute([$email]);
if ($stmt2->fetch()) {
    echo json_encode(['ok'=>false,'msg'=>'Cette adresse email est déjà utilisée par un autre utilisateur.']);
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

// APRÈS
echo json_encode(['ok'=>true,'msg'=>'Utilisateur ajouté avec succès.']);
exit;
