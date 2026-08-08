<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Vérification de la session
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    $eid = $_SESSION['eid'];

    // Traitement de l'envoi d'un nouveau message
    if (isset($_POST['send'])) {
        $message = trim($_POST['message']);
        $sender = 'Employé';
        
        if (!empty($message)) {
            // Création de la table tblmessages si elle n'existe pas déjà (sécurité structurelle)
            $checkTable = "CREATE TABLE IF NOT EXISTS tblmessages (
                id int(11) NOT NULL AUTO_INCREMENT,
                emp_id int(11) NOT NULL,
                message text NOT NULL,
                sender varchar(50) NOT NULL,
                posting_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                is_read int(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $dbh->exec($checkTable);

            $sql = "INSERT INTO tblmessages (emp_id, message, sender) VALUES (:eid, :message, :sender)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':eid', $eid, PDO::PARAM_INT);
            $query->bindParam(':message', $message, PDO::PARAM_STR);
            $query->bindParam(':sender', $sender, PDO::PARAM_STR);
            $query->execute();
            
            // Redirection pour éviter la double soumission du formulaire
            header("Location: chatwith-admin.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Contacter l'Administration</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
        <style>
            .chat-box {
                height: 400px;
                overflow-y: auto;
                padding: 20px;
                background-color: #f5f5f5;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
            }
            .message {
                margin-bottom: 15px;
                display: flex;
                flex-direction: column;
            }
            .message-content {
                max-width: 75%;
                padding: 12px 18px;
                border-radius: 20px;
                font-size: 14px;
                line-height: 1.4;
                word-wrap: break-word;
            }
            .message.employee {
                align-items: flex-end;
            }
            .message.employee .message-content {
                background-color: #00acc1;
                color: white;
                border-bottom-right-radius: 5px;
            }
            .message.admin {
                align-items: flex-start;
            }
            .message.admin .message-content {
                background-color: #e0e0e0;
                color: #333;
                border-bottom-left-radius: 5px;
            }
            .message-time {
                font-size: 11px;
                color: #9e9e9e;
                margin-top: 5px;
            }
            .send-btn-container {
                display: flex;
                align-items: center;
            }
            .send-btn-container button {
                width: 100%;
                height: 3rem;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Assistance & Support RH</div>
                </div>
                <div class="col s12 m12 l10 offset-l1">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Conversation avec l'administration</span>
                            
                            <!-- Fenêtre de discussion -->
                            <div class="chat-box" id="chatBox">
                                <?php 
                                // Récupération de l'historique des messages
                                // On utilise un try/catch pour éviter une erreur fatale si la table n'existe pas encore
                                try {
                                    $sql = "SELECT message, sender, posting_date FROM tblmessages WHERE emp_id = :eid ORDER BY posting_date ASC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $result) { 
                                            $isEmployee = ($result->sender === 'Employé');
                                            $msgClass = $isEmployee ? 'employee' : 'admin';
                                ?>
                                            <div class="message <?php echo $msgClass; ?>">
                                                <div class="message-content">
                                                    <?php echo nl2br(htmlentities($result->message)); ?>
                                                </div>
                                                <div class="message-time">
                                                    <?php echo htmlentities(date("d/m/Y H:i", strtotime($result->posting_date))); ?>
                                                </div>
                                            </div>
                                <?php 
                                        }
                                    } else {
                                        echo "<div class='center-align grey-text' style='margin-top: 150px;'>Envoyez votre premier message pour démarrer la conversation.</div>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<div class='center-align grey-text' style='margin-top: 150px;'>Envoyez votre premier message pour démarrer la conversation.</div>";
                                }
                                ?>
                            </div>

                            <!-- Zone de saisie -->
                            <div class="row" style="margin-bottom: 0;">
                                <form name="chatform" method="post" action="">
                                    <div class="input-field col s9 m10">
                                        <textarea id="message" name="message" class="materialize-textarea" placeholder="Saisissez votre message ici..." required style="padding-top: 10px;"></textarea>
                                    </div>
                                    <div class="input-field col s3 m2 send-btn-container">
                                        <button type="submit" name="send" class="waves-effect waves-light btn cyan darken-1" style="border-radius: 25px;">
                                            <i class="material-icons">send</i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script>
            // Faire défiler automatiquement vers le dernier message
            $(document).ready(function() {
                var chatBox = document.getElementById("chatBox");
                chatBox.scrollTop = chatBox.scrollHeight;
                
                // Soumettre le formulaire en appuyant sur "Entrée" (sans Maj)
                $('#message').keypress(function(e) {
                    if (e.which == 13 && !e.shiftKey) {
                        e.preventDefault();
                        $('form[name="chatform"]').submit();
                    }
                });
            });
        </script>
    </body>
</html>
<?php } ?>