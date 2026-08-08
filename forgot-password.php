<?php
session_start();
error_reporting(0);
include('includes/config.php');

$error = "";
$msg = "";

if (isset($_POST['change'])) {
    $email = $_POST['email'];
    $empid = $_POST['empid'];
    $newpassword = $_POST['newpassword'];
    $confirmpassword = $_POST['confirmpassword'];

    if ($newpassword !== $confirmpassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérification de l'existence de l'employé avec cet email et ce matricule
        $sql = "SELECT id FROM tblemployees WHERE EmailId = :email AND EmpId = :empid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            // Hachage du nouveau mot de passe
            $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);
            
            // Mise à jour du mot de passe
            $updateSql = "UPDATE tblemployees SET Password = :password WHERE EmailId = :email AND EmpId = :empid";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $updateQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $updateQuery->bindParam(':empid', $empid, PDO::PARAM_STR);
            $updateQuery->execute();
            
            $msg = "Votre mot de passe a été réinitialisé avec succès.";
        } else {
            $error = "Les informations fournies ne correspondent à aucun compte.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Mot de passe oublié</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">        
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <script type="text/javascript">
            function valid() {
                if(document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
                    alert("Le nouveau mot de passe et la confirmation ne correspondent pas !");
                    document.chngpwd.confirmpassword.focus();
                    return false;
                }
                return true;
            }
        </script>
    </head>
    <body class="signin-page">
        <div class="mn-content valign-wrapper">
            <main class="mn-inner container">
                <div class="valign">
                    <div class="row">
                        <div class="col s12 m8 l6 offset-m2 offset-l3">
                            <div class="card white darken-1">
                                <div class="card-content">
                                    <span class="card-title" style="font-size: 20px; font-weight: bold; color: #00838f;">Réinitialisation du mot de passe</span>
                                    <p class="grey-text text-darken-1 margin-b-md">Veuillez renseigner vos informations pour créer un nouveau mot de passe.</p>
                                    
                                    <?php if ($error) { ?>
                                        <div class="card-panel red lighten-4 red-text text-darken-4" style="padding: 10px; margin-bottom: 15px;">
                                            <?php echo htmlentities($error); ?>
                                        </div>
                                    <?php } ?>
                                    
                                    <?php if ($msg) { ?>
                                        <div class="card-panel green lighten-4 green-text text-darken-4" style="padding: 10px; margin-bottom: 15px;">
                                            <?php echo htmlentities($msg); ?>
                                            <br><a href="index.php" class="green-text text-darken-4" style="text-decoration: underline; font-weight: bold;">Retourner à la page de connexion</a>
                                        </div>
                                    <?php } ?>

                                    <div class="row">
                                        <form class="col s12" name="chngpwd" method="post" onSubmit="return valid();">
                                            <div class="input-field col s12 m6">
                                                <input id="email" type="email" name="email" class="validate" autocomplete="off" required>
                                                <label for="email">Adresse Email</label>
                                            </div>
                                            <div class="input-field col s12 m6">
                                                <input id="empid" type="text" name="empid" class="validate" autocomplete="off" required>
                                                <label for="empid">Matricule Employé</label>
                                            </div>
                                            <div class="input-field col s12 m6">
                                                <input id="newpassword" type="password" name="newpassword" class="validate" required>
                                                <label for="newpassword">Nouveau mot de passe</label>
                                            </div>
                                            <div class="input-field col s12 m6">
                                                <input id="confirmpassword" type="password" name="confirmpassword" class="validate" required>
                                                <label for="confirmpassword">Confirmer le mot de passe</label>
                                            </div>
                                            
                                            <div class="col s12 center-align" style="margin-top: 20px;">
                                                <button type="submit" name="change" class="waves-effect waves-light btn cyan darken-1" style="width: 100%;">
                                                    Mettre à jour
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="center-align">
                                <a href="index.php" class="cyan-text text-darken-3" style="font-size: 13px; font-weight: 500;">← Retour à la connexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
    </body>
</html>