<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    $did = intval($_GET['deptid']);
    
    if(isset($_POST['update'])) {
        $deptname = trim($_POST['departmentname']);
        $deptshortname = trim($_POST['departmentshortname']);
        $deptcode = trim($_POST['deptcode']);   
        
        $sql = "UPDATE tbldepartments SET DepartmentName=:deptname, DepartmentShortName=:deptshortname, DepartmentCode=:deptcode WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':deptname', $deptname, PDO::PARAM_STR);
        $query->bindParam(':deptshortname', $deptshortname, PDO::PARAM_STR);
        $query->bindParam(':deptcode', $deptcode, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();
        
        $msg = "Le service a été mis à jour avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Modifier un service</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
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
                    <div class="page-title">Mettre à jour le service</div>
                </div>
                <div class="col s12 m12 l6 offset-l3">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Édition du service</span>
                            
                            <?php if($msg){?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php }?>
                            
                            <div class="row">
                                <form class="col s12" name="chngpwd" method="post">
                                    <?php 
                                    $sql = "SELECT * from tbldepartments WHERE id=:did";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':did', $did, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    if($query->rowCount() > 0) {
                                        foreach($results as $result) { ?>
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input id="departmentname" type="text" class="validate" autocomplete="off" name="departmentname" value="<?php echo htmlentities($result->DepartmentName);?>" required>
                                                    <label for="departmentname">Nom du service</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input id="departmentshortname" type="text" class="validate" autocomplete="off" name="departmentshortname" value="<?php echo htmlentities($result->DepartmentShortName);?>" required>
                                                    <label for="departmentshortname">Nom court (Abréviation)</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input id="deptcode" type="text" class="validate" autocomplete="off" name="deptcode" value="<?php echo htmlentities($result->DepartmentCode);?>" required>
                                                    <label for="deptcode">Code du service</label>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                    <div class="row">
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="update" class="waves-effect waves-light btn cyan darken-1" style="width: 200px;">
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
