<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['emplogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    $eid = $_SESSION['eid'];

    // Action : Marquer un message comme lu
    if(isset($_GET['read'])) {
        $msg_id = intval($_GET['read']);
        $sql_read = "UPDATE tblmessages SET is_read=1 WHERE id=:msg_id AND emp_id=:eid";
        $query_read = $dbh->prepare($sql_read);
        $query_read->bindParam(':msg_id', $msg_id, PDO::PARAM_INT);
        $query_read->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query_read->execute();
        
        // Redirection propre pour nettoyer l'URL
        header('location:messages.php');
        exit();
    }

    // Récupération de tous les messages de l'employé connecté
    $sql = "SELECT id, sender, message, posting_date, is_read 
            FROM tblmessages 
            WHERE emp_id=:eid 
            ORDER BY posting_date DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Mes Messages</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            .message-card { transition: all 0.3s ease; border-left: 5px solid #ccc; }
            .message-card.unread { border-left-color: #ff9800; background-color: #fffde7; }
            .message-card.read { border-left-color: #4caf50; background-color: #ffffff; }
            .message-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .message-body { font-size: 15px; color: #333; line-height: 1.6; white-space: pre-line; }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Mes Messages & Notifications</div>
                </div>
                
                <div class="col s12 m12 l10 offset-l1">
                    <?php 
                    if($query->rowCount() > 0) {
                        foreach($results as $result) { 
                            $is_unread = ($result->is_read == 0);
                    ?>
                        <div class="card message-card <?php echo $is_unread ? 'unread' : 'read'; ?>">
                            <div class="card-content">
                                <div class="message-header">
                                    <div>
                                        <strong style="font-size: 16px; color: #00838f;">
                                            <i class="material-icons tiny">person</i> <?php echo htmlentities($result->sender); ?>
                                        </strong>
                                        <span style="font-size: 12px; color: #757575; margin-left: 15px;">
                                            <i class="material-icons tiny">access_time</i> <?php echo htmlentities(date('d/m/Y à H:i', strtotime($result->posting_date))); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <?php if($is_unread) { ?>
                                            <span class="chip orange white-text">Nouveau</span>
                                            <a href="messages.php?read=<?php echo htmlentities($result->id); ?>" class="waves-effect waves-light btn-small btn-flat cyan-text" title="Marquer comme lu">
                                                <i class="material-icons left">drafts</i> Marquer comme lu
                                            </a>
                                        <?php } else { ?>
                                            <span class="chip green white-text">Lu</span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 10px 0;">
                                <div class="message-body">
                                    <?php echo htmlentities($result->message); ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    } else { 
                    ?>
                        <div class="card">
                            <div class="card-content center-align" style="padding: 40px;">
                                <i class="material-icons large grey-text text-lighten-1">email</i>
                                <h5>Aucun message reçu</h5>
                                <p class="grey-text">Vous n'avez aucun message ou notification dans votre boîte de réception pour le moment.</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
    </body>
</html>
<?php } ?>