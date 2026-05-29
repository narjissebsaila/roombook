<?php
/*
    Fichier : auth/guard.php
    Rôle    : protéger les pages — vérifier qu'un utilisateur est connecté.
    À inclure en haut de chaque page protégée.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pas connecté → redirection vers la page de login
if (!isset($_SESSION["id"])) {
    header("Location: ../auth/login.php");
    exit;
}
