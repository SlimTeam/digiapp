<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    if(isset($_POST['send'])) {
        $empid = $_POST['empid'];
        $message = trim($_POST['message']);
        $sender = "Admin";
        
        // Adapté à votre structure SQL : emp_id, message, sender, is_read
        $sql = "INSERT INTO tblmessages (emp_id, message, sender, is_read) VALUES (:empid, :message, :sender, 0)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_INT);
        $query->bindParam(':message', $message, PDO::PARAM_STR);
        $query->bindParam(':sender', $sender, PDO::PARAM_STR);
        $query->execute();
        
        $lastInsertId = $dbh->lastInsertId();
        if($lastInsertId) {
            $msg = "Le message a été envoyé avec succès à l'employé.";
        } else {
            $error = "Une erreur s'est produite lors de l'envoi du message.";
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Messagerie Employés</title>
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
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Envoyer un message à un employé</div>
                </div>
                <div class="col s12 m12 l6 offset-l3">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Nouveau message</span>
                            
                            <?php if(isset($error) && $error){?>
                                <div class="errorWrap"><strong>Erreur</strong> : <?php echo htmlentities($error); ?> </div>
                            <?php } else if(isset($msg) && $msg){?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php }?>
                            
                            <div class="row">
                                <form class="col s12" name="chat" method="post">
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <select name="empid" required>
                                                <option value="" disabled selected>Sélectionnez un employé...</option>
                                                <?php 
                                                $sql = "SELECT id, FirstName, LastName, EmpId FROM tblemployees WHERE Status=1";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result) { ?>
                                                        <option value="<?php echo htmlentities($result->id);?>">
                                                            <?php echo htmlentities($result->FirstName." ".$result->LastName);?> (<?php echo htmlentities($result->EmpId);?>)
                                                        </option>
                                                <?php }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <textarea id="message" name="message" class="materialize-textarea" length="1000" required></textarea>
                                            <label for="message">Votre message</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="send" class="waves-effect waves-light btn cyan darken-1" style="width: 200px;">
                                                Envoyer <i class="material-icons right">send</i>
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
        <script>
            $(document).ready(function() {
                $('select').material_select();
            });
        </script>
    </body>
</html>
<?php } ?>