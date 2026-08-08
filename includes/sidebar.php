<aside id="slide-out" class="side-nav white fixed">
    <div class="sidebar-profile" style="background: linear-gradient(135deg, #00acc1 0%, #00838f 100%);">
        <div class="sidebar-profile-image">
            <?php
            $eid = $_SESSION['eid'];
            $sql = "SELECT FirstName, LastName, EmpId, Department FROM tblemployees WHERE id = :eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':eid', $eid, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);

            if ($query->rowCount() > 0) {
                $fullName = htmlentities($result->FirstName . " " . $result->LastName);
                $empCode = htmlentities($result->EmpId);
            } else {
                $fullName = "Employé";
                $empCode = "N/A";
            }
            ?>
            <img src="assets/images/profile-image.png" class="circle" alt="Photo de profil">
        </div>
        <div class="sidebar-profile-info">
            <p><?php echo $fullName; ?></p>
            <span style="color: #e0f7fa; font-size: 12px;">Matricule : <?php echo $empCode; ?></span>
        </div>
    </div>
    <ul class="sidebar-menu collapsible collapsible-accordion" data-collapsible="accordion">
        <li class="no-padding">
            <a class="waves-effect waves-grey" href="myprofile.php">
                <i class="material-icons">account_circle</i>Mon Profil
            </a>
        </li>
        <li class="no-padding">
            <a class="waves-effect waves-grey" href="emp-changepassword.php">
                <i class="material-icons">lock</i>Mot de passe
            </a>
        </li>
        <li class="no-padding">
            <a class="collapsible-header waves-effect waves-grey">
                <i class="material-icons">event_note</i>Mes Congés<i class="material-icons right">arrow_drop_down</i>
            </a>
            <div class="collapsible-body">
                <ul>
                    <li><a href="apply-leave.php">Demander un congé</a></li>
                    <li><a href="leavehistory.php">Historique des congés</a></li>
                </ul>
            </div>
        </li>
        <li class="no-padding">
            <a class="waves-effect waves-grey" href="chatwith-admin.php">
                <i class="material-icons">chat</i>Contact Admin
            </a>
        </li>
        <li class="no-padding">
    <a href="messages.php" class="waves-effect waves-grey">
        <i class="material-icons">chat</i>Mes Messages
    </a>
</li>
        <li class="no-padding">
            <a class="waves-effect waves-grey" href="logout.php">
                <i class="material-icons">exit_to_app</i>Déconnexion
            </a>
        </li>
    </ul>
    <div class="footer">
        <p class="copyright">DigiApp Services © <?php echo date('Y'); ?></p>
    </div>
</aside>