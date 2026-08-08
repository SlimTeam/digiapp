<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    if(isset($_POST['submit'])) {
        $emp_id = $_POST['emp_id'];
        $amount = $_POST['amount'];
        $advance_month = $_POST['advance_month'];
        $reason = $_POST['reason'];

        $sql = "INSERT INTO tblsalaryadvances(emp_id, amount, advance_month, reason) VALUES(:emp_id, :amount, :advance_month, :reason)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':advance_month', $advance_month, PDO::PARAM_STR);
        $query->bindParam(':reason', $reason, PDO::PARAM_STR);
        $query->execute();

        $msg = "Avance ajoutée avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Saisir une Avance</title>
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
                <div class="col s12 m12 l8 offset-l2">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">Enregistrer une Avance sur Salaire</span>
                            
                            <?php if(isset($msg)) { ?>
                                <div class="succWrap" style="padding:10px; background:#e8f5e9; border-left:4px solid #4caf50; margin-bottom:15px;">
                                    <strong>Succès</strong> : <?php echo htmlentities($msg); ?>
                                </div>
                            <?php } ?>

                            <form method="post">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <select name="emp_id" required>
                                            <option value="" disabled selected>Choisir un employé</option>
                                            <?php 
                                            $sql_emp = "SELECT id, FirstName, LastName, EmpId FROM tblemployees ORDER BY FirstName ASC";
                                            $q_emp = $dbh->prepare($sql_emp);
                                            $q_emp->execute();
                                            $employees = $q_emp->fetchAll(PDO::FETCH_OBJ);
                                            foreach($employees as $emp) {
                                                echo "<option value='".$emp->id."'>".htmlentities($emp->FirstName." ".$emp->LastName)." (".$emp->EmpId.")</option>";
                                            }
                                            ?>
                                        </select>
                                        <label>Employé</label>
                                    </div>

                                    <div class="input-field col s12 m6">
                                        <input id="amount" type="number" step="0.01" name="amount" required>
                                        <label for="amount">Montant de l'avance (TND)</label>
                                    </div>

                                    <div class="input-field col s12 m6">
                                        <input id="advance_month" type="month" name="advance_month" value="<?php echo date('Y-m'); ?>" required>
                                        <label for="advance_month" class="active">Mois concerné</label>
                                    </div>

                                    <div class="input-field col s12">
                                        <textarea id="reason" name="reason" class="materialize-textarea"></textarea>
                                        <label for="reason">Motif / Remarques</label>
                                    </div>

                                    <div class="input-field col s12">
                                        <button type="submit" name="submit" class="waves-effect waves-light btn cyan darken-1">Enregistrer</button>
                                        <a href="manage-advances.php" class="waves-effect waves-light btn grey">Retour</a>
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
        <script src="../assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('select').material_select();
            });
        </script>
    </body>
</html>
<?php } ?>