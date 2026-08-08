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
    $msg = "";
    $error = "";

    // Traitement de la mise à jour du profil
    if (isset($_POST['update'])) {
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];
        $gender = $_POST['gender'];
        $dob = date('j F, Y', strtotime($_POST['dob']));
        $department = $_POST['department'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        $mobileno = $_POST['mobileno'];

        $sql = "UPDATE tblemployees SET FirstName=:fname, LastName=:lname, Gender=:gender, Dob=:dob, Department=:department, Address=:address, City=:city, Country=:country, Phonenumber=:mobileno WHERE id=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':country', $country, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query->execute();
        
        $msg = "Votre profil a été mis à jour avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Mon Profil</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
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
                    <div class="page-title">Mon Profil</div>
                </div>
                <div class="col s12 m12 l12">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title" style="color: #00838f; font-weight: bold;">Informations Personnelles</span>
                            
                            <?php if ($error) { ?>
                                <div class="errorWrap"><strong>Erreur</strong> : <?php echo htmlentities($error); ?> </div>
                            <?php } else if ($msg) { ?>
                                <div class="succWrap"><strong>Succès</strong> : <?php echo htmlentities($msg); ?> </div>
                            <?php } ?>
                            
                            <div class="row">
                                <form class="col s12" name="updatemp" method="post">
                                    <?php 
                                    $eid = $_SESSION['eid'];
                                    $sql = "SELECT * from tblemployees where id=:eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $result) { ?> 

                                            <div class="row">
                                                <div class="input-field col s12 m6">
                                                    <input id="empcode" type="text" value="<?php echo htmlentities($result->EmpId); ?>" readonly disabled>
                                                    <label for="empcode">Matricule Employé</label>
                                                </div>
                                                <div class="input-field col s12 m6">
                                                    <input id="email" type="email" value="<?php echo htmlentities($result->EmailId); ?>" readonly disabled>
                                                    <label for="email">Adresse Email</label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="input-field col s12 m6">
                                                    <input id="firstName" name="firstName" type="text" value="<?php echo htmlentities($result->FirstName); ?>" required>
                                                    <label for="firstName">Prénom</label>
                                                </div>
                                                <div class="input-field col s12 m6">
                                                    <input id="lastName" name="lastName" type="text" value="<?php echo htmlentities($result->LastName); ?>" required>
                                                    <label for="lastName">Nom de famille</label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="input-field col s12 m6">
                                                    <select name="gender" required>
                                                        <option value="Homme" <?php echo (($result->Gender == 'Homme') || ($result->Gender == 'Male')) ? 'selected' : ''; ?>>Homme</option>
                                                        <option value="Femme" <?php echo (($result->Gender == 'Femme') || ($result->Gender == 'Female')) ? 'selected' : ''; ?>>Femme</option>
                                                    </select>
                                                    <label>Sexe</label>
                                                </div>
                                                <div class="input-field col s12 m6">
                                                    <input id="dob" name="dob" type="date" value="<?php echo htmlentities(date('Y-m-d', strtotime($result->Dob))); ?>" required>
                                                    <label for="dob" <?php echo !empty($result->Dob) ? 'class="active"' : ''; ?>>Date de Naissance</label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="input-field col s12 m6">
                                                    <select name="department" required>
                                                        <option value="<?php echo htmlentities($result->Department); ?>" selected><?php echo htmlentities($result->Department); ?></option>
                                                        <?php 
                                                        $sqldept = "SELECT DepartmentName from tbldepartments";
                                                        $querydept = $dbh->prepare($sqldept);
                                                        $querydept->execute();
                                                        $resultsdept = $querydept->fetchAll(PDO::FETCH_OBJ);
                                                        if ($querydept->rowCount() > 0) {
                                                            foreach ($resultsdept as $resultdept) {
                                                                if ($resultdept->DepartmentName != $result->Department) {
                                                        ?>
                                                                    <option value="<?php echo htmlentities($resultdept->DepartmentName); ?>"><?php echo htmlentities($resultdept->DepartmentName); ?></option>
                                                        <?php 
                                                                }
                                                            }
                                                        } 
                                                        ?>
                                                    </select>
                                                    <label>Département</label>
                                                </div>
                                                <div class="input-field col s12 m6">
                                                    <input id="mobileno" name="mobileno" type="text" value="<?php echo htmlentities($result->Phonenumber); ?>" maxlength="15" required>
                                                    <label for="mobileno">Téléphone</label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="input-field col s12">
                                                    <input id="address" name="address" type="text" value="<?php echo htmlentities($result->Address); ?>" required>
                                                    <label for="address">Adresse</label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="input-field col s12 m6">
                                                    <input id="city" name="city" type="text" value="<?php echo htmlentities($result->City); ?>" required>
                                                    <label for="city">Ville</label>
                                                </div>
                                                <div class="input-field col s12 m6">
                                                    <input id="country" name="country" type="text" value="<?php echo htmlentities($result->Country); ?>" required>
                                                    <label for="country">Pays</label>
                                                </div>
                                            </div>

                                    <?php 
                                        } 
                                    } 
                                    ?>
                                    
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

        <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('select').material_select();
                // Assurer que les labels s'affichent correctement si les champs sont préremplis
                Materialize.updateTextFields();
            });
        </script>
    </body>
</html>
<?php } ?>