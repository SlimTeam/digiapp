<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    $isread = 1;
    $did = intval($_GET['leaveid']);  
    date_default_timezone_set('Europe/Paris');
    $admremarkdate = date('Y-m-d H:i:s');

    // Marquer la demande comme lue
    $sql = "UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isread', $isread, PDO::PARAM_INT);
    $query->bindParam(':did', $did, PDO::PARAM_INT);
    $query->execute();

    // Traitement de l'approbation / refus
    if(isset($_POST['update'])) { 
        $did = intval($_GET['leaveid']);
        $description = $_POST['description'];
        $status = $_POST['status'];   

        $sql = "UPDATE tblleaves SET AdminRemark=:description, Status=:status, AdminRemarkDate=:admremarkdate WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->bindParam(':admremarkdate', $admremarkdate, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();
        
        $msg = "La demande de congé a été traitée avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Détails du congé</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
         <style>
            .errorWrap { padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #dd3d36; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); border-radius: 6px; }
            .succWrap{ padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #5cb85c; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); border-radius: 6px; }

            .leave-details-wrapper {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                padding: 24px;
            }
            .leave-details-wrapper .leave-details-title {
                font-size: 20px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .leave-details-wrapper .leave-details-title::before {
                content: '';
                display: inline-block;
                width: 4px;
                height: 22px;
                background: #3498db;
                border-radius: 2px;
            }
            .leave-details-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 6px;
            }
            .leave-details-table th {
                background: #f8f9fa;
                color: #7f8c8d;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                padding: 10px 16px;
                border: none;
                border-top-left-radius: 6px;
                border-bottom-left-radius: 6px;
                width: 25%;
            }
            .leave-details-table td {
                background: #fff;
                padding: 10px 16px;
                border: none;
                border-top-right-radius: 6px;
                border-bottom-right-radius: 6px;
                font-size: 14px;
                box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.05);
            }
            .leave-details-table tbody tr:hover th,
            .leave-details-table tbody tr:hover td {
                background: #f8f9fa;
            }
            .leave-details-table tr:last-child td,
            .leave-details-table tr:last-child th {
                border-bottom-left-radius: 6px;
                border-bottom-right-radius: 6px;
            }
            .status-badge {
                display: inline-block;
                padding: 5px 14px;
                border-radius: 20px;
                color: #fff;
                font-weight: 600;
                font-size: 12px;
            }
            .status-approved { background: linear-gradient(135deg, #27ae60, #2ecc71); }
            .status-rejected { background: linear-gradient(135deg, #c0392b, #e74c3c); }
            .status-pending { background: linear-gradient(135deg, #2980b9, #3498db); }
            .action-btn {
                display: inline-block;
                padding: 8px 20px;
                border-radius: 6px;
                background: #3498db;
                color: #fff;
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.2s ease;
                border: none;
            }
            .action-btn:hover {
                background: #2980b9;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }
            .notice-box {
                padding: 14px 18px;
                border-radius: 8px;
                font-size: 13px;
                color: #555;
            }
            .notice-info {
                background: #eaf2f8;
                border-left: 4px solid #3498db;
            }
            .form-action-btn {
                padding: 10px 30px;
                border-radius: 6px;
                background: #00bcd4;
                color: #fff;
                font-weight: 500;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .form-action-btn:hover {
                background: #00838f;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title" style="font-size:24px;">Détails de la demande de congé</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="leave-details-wrapper">
                        <div class="leave-details-title">Détails de la demande de congé</div>
                
                        <?php if($msg){?>
                            <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                        <?php }?>
                        
                        <?php 
                        $lid = intval($_GET['leaveid']);
                        $sql = "SELECT tblleaves.id as lid, tblemployees.FirstName, tblemployees.LastName, tblemployees.EmpId, tblemployees.id, tblemployees.Gender, tblemployees.Phonenumber, tblemployees.EmailId, tblleaves.LeaveType, tblleaves.ToDate, tblleaves.FromDate, tblleaves.Description, tblleaves.PostingDate, tblleaves.Status, tblleaves.AdminRemark, tblleaves.AdminRemarkDate FROM tblleaves JOIN tblemployees ON tblleaves.empid=tblemployees.id WHERE tblleaves.id=:lid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':lid', $lid, PDO::PARAM_INT);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        if($query->rowCount() > 0) {
                            foreach($results as $result) { ?>
                            
                            <table class="leave-details-table">
                                <tbody>
                                    <tr>
                                        <th>Nom de l'employé</th>
                                        <td><?php echo htmlentities($result->FirstName." ".$result->LastName);?></td>
                                        <th>Matricule</th>
                                        <td><?php echo htmlentities($result->EmpId);?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlentities($result->EmailId);?></td>
                                        <th>Téléphone</th>
                                        <td><?php echo htmlentities($result->Phonenumber);?></td>
                                    </tr>
                                    <tr>
                                        <th>Type de congé</th>
                                        <td><?php echo htmlentities($result->LeaveType);?></td>
                                        <th>Date de la demande</th>
                                        <td><?php echo htmlentities(date("d/m/Y H:i", strtotime($result->PostingDate)));?></td>
                                    </tr>
                                    <tr>
                                        <th>Période du congé</th>
                                        <td colspan="3">
                                            Du <b><?php echo htmlentities(date("d/m/Y", strtotime($result->FromDate)));?></b> 
                                            au <b><?php echo htmlentities(date("d/m/Y", strtotime($result->ToDate)));?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Motif (Description)</th>
                                        <td colspan="3"><?php echo htmlentities($result->Description);?></td>
                                    </tr>
                                    <tr>
                                        <th>Statut actuel</th>
                                        <td colspan="3">
                                            <?php 
                                            $stats = $result->Status;
                                            if($stats == 1) { ?>
                                                <span class="status-badge status-approved">Approuvé</span>
                                            <?php } else if($stats == 2) { ?>
                                                <span class="status-badge status-rejected">Refusé</span>
                                            <?php } else { ?>
                                                <span class="status-badge status-pending">En attente de décision</span>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                    <?php if($stats != 0) { ?>
                                    <tr>
                                        <th>Remarque de l'Administrateur</th>
                                        <td colspan="3">
                                            <?php 
                                            if($result->AdminRemark == "") {
                                                echo '<span class="notice-box notice-info">Aucune remarque.</span>';
                                            } else {
                                                echo htmlentities($result->AdminRemark);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date d'action de l'Admin</th>
                                        <td colspan="3"><?php echo htmlentities(date("d/m/Y H:i", strtotime($result->AdminRemarkDate)));?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <?php 
                            // Si la demande est toujours en attente, afficher le formulaire d'action
                            if($stats == 0) { ?>
                                <div class="row" style="margin-top: 30px;">
                                    <form name="adminaction" method="post">
                                        <div class="input-field col s12">
                                            <select name="status" required>
                                                <option value="" disabled selected>Choisissez une action...</option>
                                                <option value="1">Approuver la demande</option>
                                                <option value="2">Refuser la demande</option>
                                            </select>
                                        </div>
                                        <div class="input-field col s12">
                                            <textarea id="description" name="description" class="materialize-textarea" length="500" placeholder="Ajoutez une remarque explicative (optionnel)"></textarea>
                                            <label for="description">Remarque de l'Administrateur</label>
                                        </div>
                                        <div class="input-field col s12 center-align">
                                            <button type="submit" name="update" class="form-action-btn">
                                                Valider la décision
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php } ?>
                        <?php } 
                        } ?>
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