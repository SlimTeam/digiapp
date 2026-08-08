<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Vérification de la session
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    $error = "";
    $msg = "";

    // Traitement de la demande de congé
    if (isset($_POST['apply'])) {
        $empid = $_SESSION['eid'];
        $leavetype = $_POST['leavetype'];
        $fromdate = $_POST['fromdate'];
        $todate = $_POST['todate'];
        $description = $_POST['description'];
        $status = 0; // 0 = En attente
        $isread = 0; // 0 = Non lu par l'admin

        // Validation simple des dates
        if ($fromdate > $todate) {
            $error = "La date de fin ne peut pas être antérieure à la date de début.";
        } else {
            $sql = "INSERT INTO tblleaves(LeaveType, ToDate, FromDate, Description, Status, IsRead, empid) VALUES(:leavetype, :todate, :fromdate, :description, :status, :isread, :empid)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
            $query->bindParam(':todate', $todate, PDO::PARAM_STR);
            $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_INT);
            $query->bindParam(':isread', $isread, PDO::PARAM_INT);
            $query->bindParam(':empid', $empid, PDO::PARAM_INT);
            $query->execute();
            
            $lastInsertId = $dbh->lastInsertId();
            if ($lastInsertId) {
                $msg = "Votre demande de congé a été soumise avec succès.";
            } else {
                $error = "Une erreur s'est produite. Veuillez réessayer.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Demander un congé</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            .errorWrap {
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #dd3d36;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            }
            .succWrap{
                padding: 10px;
                margin: 0 0 20px 0;
                background: #fff;
                border-left: 4px solid #5cb85c;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Demander un congé</div>
                </div>
                <div class="col s12 m12 l8 offset-l2">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Formulaire de demande</span>
                            
                            <?php if ($error) { ?>
                                <div class="errorWrap"><strong>Erreur</strong> : <?php echo htmlentities($error); ?> </div>
                            <?php } else if ($msg) { ?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php } ?>
                            
                            <div class="row">
                                <form class="col s12" name="addleave" method="post">
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <select name="leavetype" required>
                                                <option value="" disabled selected>Sélectionnez le type de congé</option>
                                                <?php 
                                                $sql = "SELECT LeaveType FROM tblleavetype";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                if ($query->rowCount() > 0) {
                                                    foreach ($results as $result) { ?>
                                                        <option value="<?php echo htmlentities($result->LeaveType); ?>"><?php echo htmlentities($result->LeaveType); ?></option>
                                                <?php } 
                                                } ?>
                                            </select>
                                            <label>Type de congé</label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="input-field col s12 m6">
                                            <input id="fromdate" name="fromdate" type="date" required>
                                            <label for="fromdate" class="active">Date de début</label>
                                        </div>
                                        <div class="input-field col s12 m6">
                                            <input id="todate" name="todate" type="date" required>
                                            <label for="todate" class="active">Date de fin</label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="input-field col s12">
                                            <textarea id="description" name="description" class="materialize-textarea" length="500" required></textarea>
                                            <label for="description">Motif / Description</label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="apply" class="waves-effect waves-light btn cyan darken-1" style="width: 200px;">
                                                Soumettre
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

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('select').material_select();
            });
        </script>
    </body>
</html>
<?php } ?>