<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    // Suppression d'une avance
    if(isset($_GET['del'])) {
        $id = intval($_GET['del']);
        $sql = "DELETE FROM tblsalaryadvances WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $msg = "L'avance a été supprimée avec succès.";
    }

    // Filtre par mois (par défaut mois actuel : YYYY-MM)
    $selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    // Requête avec jointure pour récupérer les infos de l'employé
    $sql = "SELECT a.*, e.FirstName, e.LastName, e.EmpId 
            FROM tblsalaryadvances a 
            LEFT JOIN tblemployees e ON (e.id = a.emp_id OR e.EmpId = a.emp_id)
            WHERE a.advance_month = :adv_month 
            ORDER BY a.id DESC";

    $query = $dbh->prepare($sql);
    $query->bindParam(':adv_month', $selected_month, PDO::PARAM_STR);
    $query->execute();
    $advances = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Avances sur Salaire</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Gestion des Avances sur Salaire</div>
                </div>

                <div class="col s12 m12 l12">
                    <div class="table-modern-wrapper">
                        <div class="table-modern-title">Filtrer par Mois</div>
                            
                            <form method="get" action="manage-advances.php" class="row">
                                <div class="input-field col s12 m6">
                                    <input id="month" type="month" name="month" value="<?php echo htmlentities($selected_month); ?>">
                                    <label for="month" class="active">Mois concerné</label>
                                </div>
                                <div class="input-field col s12 m6">
                                    <button type="submit" class="form-action-btn">Consulter</button>
                                    <a href="add-advance.php" class="action-btn action-btn-success">Saisir une avance</a>
                                </div>
                            </form>

                            <?php if(isset($msg) && $msg){?>
                                <div class="succWrap" style="padding:10px; background:#e8f5e9; border-left:4px solid #4caf50; margin-bottom:15px;">
                                    <strong>Succès</strong> : <?php echo htmlentities($msg); ?> 
                                </div>
                            <?php }?>

                            <table class="striped responsive-table table-data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employé</th>
                                        <th>Montant (TND)</th>
                                        <th>Mois</th>
                                        <th>Motif</th>
                                        <th>Date d'accord</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $cnt = 1;
                                $total_amount = 0;
                                if(count($advances) > 0) {
                                    foreach($advances as $adv) { 
                                        $total_amount += $adv->amount;
                                ?>  
                                    <tr>
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td><strong><?php echo htmlentities($adv->FirstName." ".$adv->LastName);?></strong> (<?php echo htmlentities($adv->EmpId);?>)</td>
                                        <td><strong><?php echo number_format($adv->amount, 2); ?></strong></td>
                                        <td><?php echo htmlentities($adv->advance_month);?></td>
                                        <td><?php echo htmlentities($adv->reason);?></td>
                                        <td><?php echo htmlentities(date('d/m/Y', strtotime($adv->posting_date)));?></td>
                                        <td>
                                            <a href="manage-advances.php?del=<?php echo htmlentities($adv->id);?>&month=<?php echo htmlentities($selected_month);?>" onclick="return confirm('Voulez-vous supprimer cette avance ?');">
                                                <i class="material-icons action-icon danger">delete</i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $cnt++;
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7" class="center-align grey-text">Aucune avance enregistrée pour ce mois.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            
                            <?php if(count($advances) > 0) { ?>
                                <div style="margin-top:20px; font-weight:bold; font-size:16px;">
                                    Total des avances accordées pour ce mois : <span class="cyan-text darken-2"><?php echo number_format($total_amount, 2); ?> TND</span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
        </main>

        <script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="../assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="../assets/js/alpha.min.js"></script>
    </body>
</html>
<?php } ?>

