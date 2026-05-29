<?php
/*
    Fichier : auth/logout.php
    Rôle    : déconnecter l'utilisateur.
*/

session_start();
session_unset();
session_destroy();

header("Location: login.php");
exit;
