<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Congés en attente</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="../assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Congés en attente d'approbation</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="table-modern-wrapper">
                        <div class="table-modern-title">Liste des demandes en attente</div>
                            
                            
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table id="example" class="display responsive-table table-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employé</th>
                                            <th>Type de congé</th>
                                            <th>Date de demande</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $status = 0;
                                        $sql = "SELECT tblleaves.id as lid, tblemployees.FirstName, tblemployees.LastName, tblemployees.EmpId, tblleaves.LeaveType, tblleaves.PostingDate, tblleaves.Status FROM tblleaves JOIN tblemployees ON tblleaves.empid=tblemployees.id WHERE tblleaves.Status=:status ORDER BY lid DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':status', $status, PDO::PARAM_INT);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if($query->rowCount() > 0) {
                                            foreach($results as $result) { ?>
                                                <tr>
                                                    <td><b><?php echo htmlentities($cnt);?></b></td>
                                                    <td>
                                                        <a href="editemployee.php?empid=<?php echo htmlentities($result->id);?>" target="_blank">
                                                            <?php echo htmlentities($result->FirstName." ".$result->LastName);?> (<?php echo htmlentities($result->EmpId);?>)
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlentities($result->LeaveType);?></td>
                                                    <td><?php echo htmlentities(date("d/m/Y H:i", strtotime($result->PostingDate)));?></td>
                                                    <td><span class="status-badge status-pending">En attente</span></td>
                                                    <td>
                                                        <a href="leave-details.php?leaveid=<?php echo htmlentities($result->lid);?>" class="action-btn" title="Traiter la demande">
                                                            <i class="material-icons">visibility</i>
                                                        </a>
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
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json" },
                    "order": [[ 0, "desc" ]],
                    "pageLength": 10
                });
            });
        </script>
    </body>
</html>
<?php } ?>

