<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (isset($_SESSION['emplogin']) && strlen($_SESSION['emplogin']) != 0) {
    header('location:myprofile.php');
    exit();
}

$msg = "";
$error = "";

if (isset($_POST['signin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Recherche par adresse email ou par matricule (EmpId)
    $sql = "SELECT id, EmailId, Password, Status FROM tblemployees WHERE EmailId = :username OR EmpId = :username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        if ($result->Status == 0) {
            $error = "Votre compte est désactivé. Veuillez contacter le service RH.";
        } else {
            // Prise en charge de password_hash() avec rétrocompatibilité MD5 transitoire
            if (password_verify($password, $result->Password) || md5($password) === $result->Password) {
                $_SESSION['emplogin'] = $result->EmailId;
                $_SESSION['eid'] = $result->id;
                header('location:myprofile.php');
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        }
    } else {
        $error = "Identifiant ou adresse email introuvable.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Connexion Employé</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <!-- Styles -->
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">        
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="signin-page">
        <div class="mn-content valign-wrapper">
            <main class="mn-inner container">
                <div class="valign">
                    <div class="row">
                        <div class="col s12 m8 l4 offset-m2 offset-l4">
                            <div class="card white darken-1">
                                <div class="card-content">
                                    <span class="card-title" style="font-size: 20px; font-weight: bold; color: #00838f;">Portail Employé</span>
                                    <p class="grey-text text-darken-1 margin-b-sm">Connectez-vous à votre espace personnel</p>
                                    
                                    <?php if ($error) { ?>
                                        <div class="card-panel red lighten-4 red-text text-darken-4" style="padding: 10px; margin-bottom: 15px;">
                                            <?php echo htmlentities($error); ?>
                                        </div>
                                    <?php } ?>
                                    
                                    <?php if ($msg) { ?>
                                        <div class="card-panel green lighten-4 green-text text-darken-4" style="padding: 10px; margin-bottom: 15px;">
                                            <?php echo htmlentities($msg); ?>
                                        </div>
                                    <?php } ?>

                                    <div class="row">
                                        <form class="col s12" name="signin" method="post">
                                            <div class="input-field col s12">
                                                <input id="username" type="text" name="username" class="validate" autocomplete="off" required>
                                                <label for="username">Adresse Email ou Matricule</label>
                                            </div>
                                            <div class="input-field col s12">
                                                <input id="password" type="password" name="password" class="validate" required>
                                                <label for="password">Mot de passe</label>
                                            </div>
                                            <div class="col s12 right-align" style="margin-bottom: 15px;">
                                                <a href="forgot-password.php" class="grey-text text-darken-1" style="font-size: 13px;">Mot de passe oublié ?</a>
                                            </div>
                                            <div class="col s12 center-align">
                                                <button type="submit" name="signin" class="waves-effect waves-light btn cyan darken-1" style="width: 100%;">
                                                    Se connecter
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="center-align">
                                <a href="admin/" class="cyan-text text-darken-3" style="font-size: 13px; font-weight: 500;">Accès Portail Administrateur →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Javascripts -->
        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
    </body>
</html>