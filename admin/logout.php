<?php
session_start(); 
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

unset($_SESSION['alogin']);
session_destroy(); // Détruire la session
header("location:index.php"); // Redirection vers la page de connexion
exit();
?>