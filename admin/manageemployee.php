<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    // Désactiver un compte employé
    if(isset($_GET['inid'])) {
        $id = $_GET['inid'];
        $status = 0;
        $sql = "UPDATE tblemployees SET Status=:status WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->execute();
        $msg = "Le compte de l'employé a été désactivé.";
    }

    // Activer un compte employé
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $status = 1;
        $sql = "UPDATE tblemployees SET Status=:status WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->execute();
        $msg = "Le compte de l'employé a été activé.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Gérer les employés</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="../assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
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
                    <div class="page-title">Gérer les employés</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="table-modern-wrapper">
                        <div class="table-modern-title">Liste des employés</div>
                            
                        <?php if($msg){?>
                            <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                        <?php }?>
                            
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table id="example" class="display responsive-table table-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Matricule</th>
                                            <th>Nom complet</th>
                                            <th>Service</th>
                                            <th>Statut</th>
                                            <th>Date d'inscription</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $sql = "SELECT EmpId, FirstName, LastName, Department, Status, RegDate, id FROM tblemployees";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if($query->rowCount() > 0) {
                                            foreach($results as $result) { ?>
                                                <tr>
                                                    <td><b><?php echo htmlentities($cnt);?></b></td>
                                                    <td><?php echo htmlentities($result->EmpId);?></td>
                                                    <td><?php echo htmlentities($result->FirstName." ".$result->LastName);?></td>
                                                    <td><?php echo htmlentities($result->Department);?></td>
                                                    <td>
                                                        <?php if($result->Status == 1){ ?>
                                                            <span class="status-badge badge-approved">Actif</span>
                                                        <?php } else { ?>
                                                            <span class="status-badge badge-declined">Inactif</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo htmlentities(date("d/m/Y", strtotime($result->RegDate)));?></td>
                                                    <td>
                                                        <a href="editemployee.php?empid=<?php echo htmlentities($result->id);?>" class="action-btn" title="Modifier">
                                                            <i class="material-icons">edit</i>
                                                        </a>
                                                        <?php if($result->Status == 1){ ?>
                                                            <a href="manageemployee.php?inid=<?php echo htmlentities($result->id);?>" onclick="return confirm('Voulez-vous vraiment désactiver ce compte ?');" class="action-btn" title="Désactiver">
                                                                <i class="material-icons">block</i>
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="manageemployee.php?id=<?php echo htmlentities($result->id);?>" onclick="return confirm('Voulez-vous vraiment activer ce compte ?');" class="action-btn action-btn-success" title="Activer">
                                                                <i class="material-icons">check_circle</i>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php $cnt++;
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </main>

        <script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="../assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="../assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="../assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#example').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json"
                    },
                    "pageLength": 10
                });
            });
        </script>
    </body>
</html>
<?php } ?>

