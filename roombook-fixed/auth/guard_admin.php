<?php
/*
    Fichier : auth/guard_admin.php
    Rôle    : protéger les pages réservées à l'administrateur.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../pages/index.php");
    exit;
}
