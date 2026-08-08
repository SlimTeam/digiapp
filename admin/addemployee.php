<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
    exit();
} else {
    if(isset($_POST['add'])) {
        $empid = trim($_POST['empcode']);
        $fname = trim($_POST['firstName']);
        $lname = trim($_POST['lastName']);   
        $email = trim($_POST['email']); 
        $password = trim($_POST['password']); 
        $gender = trim($_POST['gender']); 
        $dob = trim($_POST['dob']) ? date('j F, Y', strtotime(trim($_POST['dob']))) : ''; 
        $department = trim($_POST['department']); 
        $address = trim($_POST['address']); 
        $city = trim($_POST['city']); 
        $country = trim($_POST['country']); 
        $mobileno = trim($_POST['mobileno']); 
        $status = 1;

        // Hachage sécurisé du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO tblemployees(EmpId, FirstName, LastName, EmailId, Password, Gender, Dob, Department, Address, City, Country, Phonenumber, Status) VALUES(:empid, :fname, :lname, :email, :password, :gender, :dob, :department, :address, :city, :country, :mobileno, :status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':country', $country, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->execute();
        
        $lastInsertId = $dbh->lastInsertId();
        if($lastInsertId) {
            $msg = "Le compte de l'employé a été créé avec succès.";
        } else {
            $error = "Une erreur s'est produite. Vérifiez que l'identifiant ou l'email n'existe pas déjà.";
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Ajouter un employé</title>
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
        
        <script type="text/javascript">
            function valid() {
                if(document.addemp.password.value != document.addemp.confirmpassword.value) {
                    alert("Le mot de passe et la confirmation ne correspondent pas.");
                    document.addemp.confirmpassword.focus();
                    return false;
                }
                return true;
            }
            
            // Vérification de la disponibilité de l'email via AJAX
            function checkAvailabilityEmailid() {
                $("#loaderIcon").show();
                jQuery.ajax({
                    url: "check_availability.php",
                    data:'emailid='+$("#email").val(),
                    type: "POST",
                    success:function(data){
                        $("#emailid-availability").html(data);
                        $("#loaderIcon").hide();
                    },
                    error:function (){}
                });
            }
        </script>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>
        
        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Créer un compte employé</div>
                </div>
                
                <div class="col s12 m12 l12">
                    <div class="card">
                        <div class="card-content">
                            <form name="addemp" method="post" onSubmit="return valid();">
                                
                                <?php if($error){?>
                                    <div class="errorWrap"><strong>Erreur</strong>: <?php echo htmlentities($error); ?> </div>
                                <?php } else if($msg){?>
                                    <div class="succWrap"><strong>Succès</strong>: <?php echo htmlentities($msg); ?> </div>
                                <?php }?>

                                <div class="row">
                                    <div class="col m6 s12">
                                        <span class="card-title" style="color: #00838f; font-weight: bold;">Informations professionnelles</span>
                                        
                                        <div class="input-field col s12">
                                            <input id="empcode" type="text" name="empcode" autocomplete="off" required>
                                            <label for="empcode">Matricule Employé (Ex: EMP102)</label>
                                        </div>
                                        
                                        <div class="input-field col m6 s12">
                                            <input id="firstName" type="text" name="firstName" autocomplete="off" required>
                                            <label for="firstName">Prénom</label>
                                        </div>
                                        
                                        <div class="input-field col m6 s12">
                                            <input id="lastName" type="text" name="lastName" autocomplete="off" required>
                                            <label for="lastName">Nom</label>
                                        </div>
                                        
                                        <div class="input-field col s12">
                                            <input id="email" type="email" name="email" onBlur="checkAvailabilityEmailid()" autocomplete="off" required>
                                            <label for="email">Adresse Email</label>
                                            <span id="emailid-availability" style="font-size:12px;"></span> 
                                        </div>

                                        <div class="input-field col s12">
                                            <input id="password" type="password" name="password" autocomplete="off" required>
                                            <label for="password">Mot de passe</label>
                                        </div>
                                        
                                        <div class="input-field col s12">
                                            <input id="confirmpassword" type="password" name="confirmpassword" autocomplete="off" required>
                                            <label for="confirmpassword">Confirmer le mot de passe</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col m6 s12">
                                        <span class="card-title" style="color: #00838f; font-weight: bold;">Informations personnelles</span>
                                        
                                        <div class="input-field col m6 s12">
                                        <select name="gender" required>
                                            <option value="" disabled selected>Sélectionnez le genre</option>
                                            <option value="Homme">Homme</option>
                                            <option value="Femme">Femme</option>
                                        </select>
                                        </div>
                                        
                                        <div class="input-field col m6 s12">
                                            <label for="dob" class="active">Date de naissance</label>
                                            <input id="dob" type="date" name="dob" autocomplete="off" required>
                                        </div>

                                        <div class="input-field col s12">
                                            <select name="department" required>
                                                <option value="" disabled selected>Affectation (Service)</option>
                                                <?php 
                                                $sql = "SELECT DepartmentName FROM tbldepartments";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result) { ?>
                                                        <option value="<?php echo htmlentities($result->DepartmentName);?>"><?php echo htmlentities($result->DepartmentName);?></option>
                                                <?php }
                                                } ?>
                                            </select>
                                        </div>

                                        <div class="input-field col s12">
                                            <input id="address" type="text" name="address" autocomplete="off" required>
                                            <label for="address">Adresse postale</label>
                                        </div>
                                        
                                        <div class="input-field col m6 s12">
                                            <input id="city" type="text" name="city" autocomplete="off" required>
                                            <label for="city">Ville</label>
                                        </div>
                                        
                                        <div class="input-field col m6 s12">
                                            <input id="country" type="text" name="country" autocomplete="off" required>
                                            <label for="country">Pays</label>
                                        </div>
                                        
                                        <div class="input-field col s12">
                                            <input id="mobileno" type="tel" name="mobileno" maxlength="15" autocomplete="off" required>
                                            <label for="mobileno">Numéro de téléphone</label>
                                        </div>
                                        
                                        <div class="input-field col s12 right-align">
                                            <button type="submit" name="add" class="waves-effect waves-light btn cyan darken-1" id="add">
                                                Enregistrer l'employé
                                            </button>
                                        </div>
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