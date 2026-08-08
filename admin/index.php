<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(isset($_POST['signin'])) {
    $uname = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:uname";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uname', $uname, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        foreach ($results as $result) {
            // Vérification du mot de passe (compatible avec le hachage moderne et l'ancien MD5)
            if (password_verify($password, $result->Password) || md5($password) === $result->Password) {
                $_SESSION['alogin'] = $_POST['username'];
                echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
            } else {
                echo "<script>alert('Mot de passe incorrect');</script>";
            }
        }
    } else {
        echo "<script>alert('Identifiant administrateur invalide');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Connexion Administrateur</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">        
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            body {
                background-color: #f4f7f6;
                display: flex;
                min-height: 100vh;
                flex-direction: column;
            }
            main {
                flex: 1 0 auto;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                width: 100%;
                max-width: 450px;
            }
            .admin-title {
                color: #d32f2f;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                text-align: center;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body class="signin-page">
        <main class="mn-inner">
            <div class="login-box">
                <div class="card white darken-1">
                    <div class="card-content">
                        <span class="card-title admin-title">Espace Administration</span>
                        <div class="row">
                            <form class="col s12" name="signin" method="post">
                                <div class="input-field col s12">
                                    <input id="username" type="text" name="username" class="validate" autocomplete="off" required>
                                    <label for="username">Nom d'utilisateur</label>
                                </div>
                                <div class="input-field col s12">
                                    <input id="password" type="password" class="validate" name="password" autocomplete="off" required>
                                    <label for="password">Mot de passe</label>
                                </div>
                                <div class="col s12 center-align" style="margin-top: 20px;">
                                    <button type="submit" name="signin" class="waves-effect waves-light btn red darken-2" style="width: 100%; border-radius: 25px;">
                                        Se connecter
                                    </button>
                                </div>
                                <div class="col s12 center-align" style="margin-top: 15px;">
                                    <a href="../index.php" class="grey-text">Retour au portail des employés</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="../assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="../assets/js/alpha.min.js"></script>
    </body>
</html>