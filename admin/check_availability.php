<?php 
require_once("../includes/config.php");

if(!empty($_POST["emailid"])) {
    $email = $_POST["emailid"];
    
    // Vérification du format de l'email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo "<span style='color:red'>Erreur : Format d'email invalide.</span>";
    } else {
        $sql = "SELECT EmailId FROM tblemployees WHERE EmailId=:email";
        $query = $dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() > 0) {
            echo "<span style='color:red'>Cet email est déjà associé à un compte.</span>";
            echo "<script>$('#add').prop('disabled',true);</script>";
        } else {
            echo "<span style='color:green'>Email disponible.</span>";
            echo "<script>$('#add').prop('disabled',false);</script>";
        }
    }
}
?>