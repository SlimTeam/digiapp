<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    $lid = intval($_GET['lid']);
    
    if(isset($_POST['update'])) {
        $leavetype = trim($_POST['leavetype']);
        $description = trim($_POST['description']);
        
        $sql = "UPDATE tblleavetype SET LeaveType=:leavetype, Description=:description WHERE id=:lid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':lid', $lid, PDO::PARAM_INT);
        $query->execute();
        
        $msg = "Le type de congé a été mis à jour avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Modifier un type de congé</title>
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
                    <div class="page-title">Mettre à jour le type de congé</div>
                </div>
                <div class="col s12 m12 l6 offset-l3">
                    <div class="form-modern-wrapper">
                        <div class="form-modern-title">Édition du type</div>
                            
                            <?php if($msg){?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php }?>
                            
                            <div class="row">
                                <form class="col s12" name="editleavetype" method="post">
                                    <?php 
                                    $sql = "SELECT * from tblleavetype WHERE id=:lid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':lid', $lid, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    if($query->rowCount() > 0) {
                                        foreach($results as $result) { ?>
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input id="leavetype" type="text" class="validate" autocomplete="off" name="leavetype" value="<?php echo htmlentities($result->LeaveType);?>" required>
                                                    <label for="leavetype">Type de congé</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <textarea id="description" name="description" class="materialize-textarea" length="500" required><?php echo htmlentities($result->Description);?></textarea>
                                                    <label for="description">Description / Remarques</label>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                    <div class="row">
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="update" class="form-submit-btn">
                                                Mettre à jour
                                            </button>
                                        </div>
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
<?php } ?>