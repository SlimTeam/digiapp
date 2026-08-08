<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    if(isset($_POST['change'])) {
        $password = $_POST['password'];
        $newpassword = $_POST['newpassword'];
        $username = $_SESSION['alogin'];
        
        // Récupération de l'ancien mot de passe en base (pour vérifier la correspondance)
        $sql = "SELECT Password FROM admin WHERE UserName=:username";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
        
        if($query->rowCount() > 0) {
            foreach($results as $result) {
                $db_password = $result->Password;
            }
            
            // Vérification de l'ancien mot de passe (compatibilité MD5 / password_hash)
            if(md5($password) == $db_password || password_verify($password, $db_password)) {
                // Hachage sécurisé du NOUVEAU mot de passe
                $hashed_newpassword = password_hash($newpassword, PASSWORD_DEFAULT);
                
                $con = "UPDATE admin SET Password=:newpassword WHERE UserName=:username";
                $chngpwd1 = $dbh->prepare($con);
                $chngpwd1->bindParam(':username', $username, PDO::PARAM_STR);
                $chngpwd1->bindParam(':newpassword', $hashed_newpassword, PDO::PARAM_STR);
                $chngpwd1->execute();
                
                $msg = "Votre mot de passe a été modifié avec succès.";
            } else {
                $error = "Le mot de passe actuel est incorrect.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Modifier le mot de passe</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            .errorWrap { padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #dd3d36; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); }
            .succWrap{ padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #5cb85c; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); }
        </style>
        
        <script type="text/javascript">
            function valid() {
                if(document.chngpwd.newpassword.value != document.chngpwd.confirmpassword.value) {
                    alert("Le nouveau mot de passe et la confirmation ne correspondent pas.");
                    document.chngpwd.confirmpassword.focus();
                    return false;
                }
                return true;
            }
        </script>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Modification du mot de passe Administrateur</div>
                </div>
                <div class="col s12 m12 l6 offset-l3">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Sécurité du compte</span>
                            
                            <?php if($error){?>
                                <div class="errorWrap"><strong>Erreur</strong> : <?php echo htmlentities($error); ?> </div>
                            <?php } else if($msg){?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php }?>
                            
                            <div class="row">
                                <form class="col s12" name="chngpwd" method="post" onSubmit="return valid();">
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <input id="password" type="password" class="validate" autocomplete="off" name="password" required>
                                            <label for="password">Mot de passe actuel</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <input id="newpassword" type="password" class="validate" autocomplete="off" name="newpassword" required>
                                            <label for="newpassword">Nouveau mot de passe</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <input id="confirmpassword" type="password" class="validate" autocomplete="off" name="confirmpassword" required>
                                            <label for="confirmpassword">Confirmer le nouveau mot de passe</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="change" class="waves-effect waves-light btn cyan darken-1" style="width: 200px;">
                                                Mettre à jour
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
<?php } ?>