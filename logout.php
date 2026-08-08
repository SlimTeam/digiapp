<?php
session_start();
error_reporting(0);

// Destruction de toutes les variables de session
$_SESSION = array();

// Si les sessions utilisent des cookies, on détruit aussi le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruction de la session
unset($_SESSION['emplogin']);
session_destroy();

// Redirection vers la page de connexion
header("location:index.php");
exit();
?>