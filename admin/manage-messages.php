<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    // Suppression d'un message
    if(isset($_GET['del'])) {
        $id = intval($_GET['del']);
        $sql = "DELETE FROM tblmessages WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $msg = "Le message a été supprimé avec succès.";
    }

    // Filtres
    $where_clauses = array();
    $params = array();

    if(!empty($_GET['employee_id'])) {
        $where_clauses[] = "emp_id = :emp_id";
        $params[':emp_id'] = $_GET['employee_id'];
    }

    if(!empty($_GET['date_from'])) {
        $where_clauses[] = "DATE(posting_date) >= :date_from";
        $params[':date_from'] = $_GET['date_from'];
    }

    if(!empty($_GET['date_to'])) {
        $where_clauses[] = "DATE(posting_date) <= :date_to";
        $params[':date_to'] = $_GET['date_to'];
    }

    // Requête DIRECTE sur tblmessages sans jointure bloquante
    $sql = "SELECT * FROM tblmessages";
    if(count($where_clauses) > 0) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    $sql .= " ORDER BY id DESC";

    $query = $dbh->prepare($sql);
    foreach($params as $key => $val) {
        $query->bindValue($key, $val);
    }
    $query->execute();
    $messages = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Gestion des Messages</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            .errorWrap { padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #dd3d36; }
            .succWrap{ padding: 10px; margin: 0 0 20px 0; background: #fff; border-left: 4px solid #5cb85c; }
            .filter-card { background-color: #fcfcfc; border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Historique & Gestion des Messages</div>
                </div>

                <div class="col s12 m12 l12">
                    <div class="table-modern-wrapper">
                        <div class="table-modern-title">Filtrer les messages</div>
                            
                            <!-- Formulaire de filtrage -->
                            <form method="get" action="manage-messages.php" class="filter-card">
                                <div class="row" style="margin-bottom: 0;">
                                    <div class="input-field col s12 m4">
                                        <select name="employee_id">
                                            <option value="">Tous les employés</option>
                                            <?php 
                                            $sql_emp = "SELECT id, FirstName, LastName, EmpId FROM tblemployees ORDER BY FirstName ASC";
                                            $query_emp = $dbh->prepare($sql_emp);
                                            $query_emp->execute();
                                            $emp_list = $query_emp->fetchAll(PDO::FETCH_OBJ);
                                            foreach($emp_list as $emp) {
                                                $selected = (isset($_GET['employee_id']) && $_GET['employee_id'] == $emp->id) ? 'selected' : '';
                                                echo "<option value='".$emp->id."' $selected>".htmlentities($emp->FirstName." ".$emp->LastName)." (".$emp->EmpId.")</option>";
                                            }
                                            ?>
                                        </select>
                                        <label>Employé</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m3">
                                        <input id="date_from" type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlentities($_GET['date_from']) : ''; ?>">
                                        <label for="date_from" class="active">Du (Date de début)</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m3">
                                        <input id="date_to" type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlentities($_GET['date_to']) : ''; ?>">
                                        <label for="date_to" class="active">Au (Date de fin)</label>
                                    </div>
                                    
                                    <div class="input-field col s12 m2 center-align">
                                        <button type="submit" class="form-action-btn" style="width: 100%;">
                                            Filtrer
                                        </button>
                                        <?php if(!empty($_GET['employee_id']) || !empty($_GET['date_from']) || !empty($_GET['date_to'])) { ?>
                                            <a href="manage-messages.php" style="display: inline-block; margin-top: 8px; font-size: 12px;">Réinitialiser</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>

                            <?php if(isset($msg) && $msg){?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php }?>

                            <!-- Tableau d'affichage -->
                            <table class="striped responsive-table table-data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Expéditeur</th>
                                        <th>Employé Destinataire</th>
                                        <th>Message</th>
                                        <th>Date d'envoi</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $cnt = 1;
                                if(count($messages) > 0) {
                                    foreach($messages as $msg_item) { 
                                        // Récupération manuelle des infos de l'employé sans casser la boucle
                                        $emp_info = "Employé #".$msg_item->emp_id;
                                        $sql_get_emp = "SELECT FirstName, LastName, EmpId FROM tblemployees WHERE id=:eid OR EmpId=:eid";
                                        $q_emp = $dbh->prepare($sql_get_emp);
                                        $q_emp->bindParam(':eid', $msg_item->emp_id);
                                        $q_emp->execute();
                                        $emp_res = $q_emp->fetch(PDO::FETCH_OBJ);
                                        if($emp_res) {
                                            $emp_info = htmlentities($emp_res->FirstName." ".$emp_res->LastName)." (".htmlentities($emp_res->EmpId).")";
                                        }
                                ?>  
                                    <tr>
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td><strong><?php echo htmlentities($msg_item->sender);?></strong></td>
                                        <td><?php echo $emp_info; ?></td>
                                        <td style="max-width: 350px; white-space: normal; word-break: break-word;">
                                            <?php echo htmlentities($msg_item->message);?>
                                        </td>
                                        <td>
                                            <?php echo htmlentities(date('d/m/Y H:i', strtotime($msg_item->posting_date)));?>
                                        </td>
                                        <td>
                                            <?php if($msg_item->is_read == 1) { ?>
                                                <span class="status-badge badge-read">Lu</span>
                                            <?php } else { ?>
                                                <span class="status-badge badge-unread">Non lu</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a href="manage-messages.php?del=<?php echo htmlentities($msg_item->id);?>" onclick="return confirm('Voulez-vous vraiment supprimer ce message ?');">
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
                                        <td colspan="7" class="center-align grey-text">Aucun message trouvé dans la base de données.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
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

