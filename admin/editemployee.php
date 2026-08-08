<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    $eid = intval($_GET['empid']);
    
    if(isset($_POST['update'])) {
        $fname = trim($_POST['firstName']);
        $lname = trim($_POST['lastName']);   
        $gender = trim($_POST['gender']); 
        $dob = date('j F, Y', strtotime(trim($_POST['dob']))); 
        $department = trim($_POST['department']); 
        $address = trim($_POST['address']); 
        $city = trim($_POST['city']); 
        $country = trim($_POST['country']); 
        $mobileno = trim($_POST['mobileno']); 

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
        
        $msg = "Les informations de l'employé ont été mises à jour avec succès.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Modifier un employé</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
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
                    <div class="page-title">Mettre à jour le profil de l'employé</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="form-modern-wrapper">
                        <div class="form-modern-title">Mettre à jour le profil de l'employé</div>
                            <form name="updatemp" method="post">
                                
                                <?php if($msg){?>
                                    <div class="succWrap"><strong>Succès</strong>: <?php echo htmlentities($msg); ?> </div>
                                <?php }?>

                                <?php 
                                $sql = "SELECT * from tblemployees WHERE id=:eid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                if($query->rowCount() > 0) {
                                    foreach($results as $result) { ?>

                                    <div class="row">
                                        <div class="col m6 s12">
                                            <span class="form-section-title">Informations professionnelles</span>
                                            
                                            <div class="input-field col s12">
                                                <input id="empcode" type="text" name="empcode" value="<?php echo htmlentities($result->EmpId);?>" readonly>
                                                <label for="empcode">Matricule Employé</label>
                                            </div>
                                            
                                            <div class="input-field col m6 s12">
                                                <input id="firstName" type="text" name="firstName" value="<?php echo htmlentities($result->FirstName);?>" required>
                                                <label for="firstName">Prénom</label>
                                            </div>
                                            
                                            <div class="input-field col m6 s12">
                                                <input id="lastName" type="text" name="lastName" value="<?php echo htmlentities($result->LastName);?>" required>
                                                <label for="lastName">Nom</label>
                                            </div>
                                            
                                            <div class="input-field col s12">
                                                <input id="email" type="email" name="email" value="<?php echo htmlentities($result->EmailId);?>" readonly>
                                                <label for="email">Adresse Email</label>
                                            </div>
                                        </div>
                                        
                                        <div class="col m6 s12">
                                            <span class="form-section-title">Informations personnelles</span>
                                            
                                            <div class="input-field col m6 s12">
                                            <select name="gender" required>
                                                <option value="<?php echo htmlentities($result->Gender);?>"><?php echo htmlentities($result->Gender);?></option>
                                                <option value="Homme">Homme</option>
                                                <option value="Femme">Femme</option>
                                            </select>
                                            </div>
                                            
                                            <div class="input-field col m6 s12">
                                                <input id="dob" type="date" name="dob" value="<?php echo htmlentities(date('Y-m-d', strtotime($result->Dob)));?>" required>
                                                <label for="dob" class="active">Date de naissance</label>
                                            </div>

                                            <div class="input-field col s12">
                                                <select name="department" required>
                                                    <option value="<?php echo htmlentities($result->Department);?>"><?php echo htmlentities($result->Department);?></option>
                                                    <?php 
                                                    $sqldept = "SELECT DepartmentName FROM tbldepartments";
                                                    $querydept = $dbh->prepare($sqldept);
                                                    $querydept->execute();
                                                    $deptresults = $querydept->fetchAll(PDO::FETCH_OBJ);
                                                    if($querydept->rowCount() > 0) {
                                                        foreach($deptresults as $deptresult) { 
                                                            if($deptresult->DepartmentName != $result->Department) { ?>
                                                                <option value="<?php echo htmlentities($deptresult->DepartmentName);?>"><?php echo htmlentities($deptresult->DepartmentName);?></option>
                                                            <?php } 
                                                        }
                                                    } ?>
                                                </select>
                                            </div>

                                            <div class="input-field col s12">
                                                <input id="address" type="text" name="address" value="<?php echo htmlentities($result->Address);?>" required>
                                                <label for="address">Adresse postale</label>
                                            </div>
                                            
                                            <div class="input-field col m6 s12">
                                                <input id="city" type="text" name="city" value="<?php echo htmlentities($result->City);?>" required>
                                                <label for="city">Ville</label>
                                            </div>
                                            
                                            <div class="input-field col m6 s12">
                                                <input id="country" type="text" name="country" value="<?php echo htmlentities($result->Country);?>" required>
                                                <label for="country">Pays</label>
                                            </div>
                                            
                                            <div class="input-field col s12">
                                                <input id="mobileno" type="tel" name="mobileno" value="<?php echo htmlentities($result->Phonenumber);?>" maxlength="15" required>
                                                <label for="mobileno">Numéro de téléphone</label>
                                            </div>
                                            
                                            <div class="input-field col s12 right-align">
                                                <button type="submit" name="update" class="form-submit-btn" id="update">
                                                    Mettre à jour
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php } } ?>
                            </form>
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