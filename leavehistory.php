<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Vérification de la session
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Historique des congés</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            /* Badges de statut modernes */
            .badge-status {
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                color: #fff;
                display: inline-block;
            }
            .badge-approved { background-color: #4caf50; }
            .badge-declined { background-color: #f44336; }
            .badge-waiting { background-color: #ff9800; }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Historique de mes demandes de congé</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="table-modern-wrapper">
                        <div class="table-modern-title">Suivi des dossiers</div>
                            
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table id="example" class="display responsive-table table-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Type de congé</th>
                                            <th>Du</th>
                                            <th>Au</th>
                                            <th>Description</th>
                                            <th>Date de demande</th>
                                            <th>Remarque Admin</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $eid = $_SESSION['eid'];
                                        $sql = "SELECT LeaveType, ToDate, FromDate, Description, PostingDate, AdminRemarkDate, AdminRemark, Status FROM tblleaves WHERE empid = :eid ORDER BY PostingDate DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $result) { ?>  
                                                <tr>
                                                    <td><b><?php echo htmlentities($cnt); ?></b></td>
                                                    <td><?php echo htmlentities($result->LeaveType); ?></td>
                                                    <td><?php echo htmlentities(date("d/m/Y", strtotime($result->FromDate))); ?></td>
                                                    <td><?php echo htmlentities(date("d/m/Y", strtotime($result->ToDate))); ?></td>
                                                    <td><?php echo htmlentities($result->Description); ?></td>
                                                    <td><?php echo htmlentities(date("d/m/Y H:i", strtotime($result->PostingDate))); ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($result->AdminRemark == "") {
                                                            echo "<span class='status-badge status-pending'>En attente d'examen</span>";
                                                        } else {
                                                            echo htmlentities($result->AdminRemark) . " <br><small><i>(" . htmlentities(date("d/m/Y", strtotime($result->AdminRemarkDate))) . ")</i></small>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $stats = $result->Status;
                                                        if ($stats == 1) {
                                                            echo '<span class="badge-status badge-approved">Approuvé</span>';
                                                        } else if ($stats == 2) {
                                                            echo '<span class="badge-status badge-declined">Refusé</span>';
                                                        } else {
                                                            echo '<span class="badge-status badge-waiting">En attente</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php 
                                            $cnt++;
                                            }
                                        } else { ?>
                                            <tr>
                                                <td colspan="8" class="center-align">Vous n'avez soumis aucune demande de congé.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </main>

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#example').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json"
                    },
                    "order": [[ 5, "desc" ]], // Trier par date de demande par défaut
                    "pageLength": 10
                });
            });
        </script>
    </body>
</html>
<?php } ?>

