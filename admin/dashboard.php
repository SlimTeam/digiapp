<?php
session_start();
error_reporting(0);
include('../includes/config.php');

// Vérification de la session administrateur
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>DigiApp Services | Tableau de Bord Administrateur</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        
        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet"> 
        <link href="../assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        
            <style>
                .dashboard-stat {
                    padding: 20px;
                    border-radius: 8px;
                    color: white;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    align-items: center;
                    text-align: center;
                }
                .stat-icon {
                    font-size: 48px;
                    opacity: 0.8;
                }
                .stat-number {
                    font-size: 32px;
                    font-weight: bold;
                    margin: 10px 0;
                }
                .stat-title {
                    font-size: 14px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .stats-cards-row {
                    display: flex;
                    align-items: stretch;
                    gap: 24px;
                }
                .stats-cards-row .col {
                    display: flex;
                    align-items: stretch;
                    flex: 1 0 0;
                }

                /* Modern leave table styles */
                .leave-table-wrapper {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    padding: 20px;
                }
                .leave-table-title {
                    font-size: 20px;
                    font-weight: 600;
                    color: #2c3e50;
                    margin-bottom: 16px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .leave-table-title::before {
                    content: '';
                    display: inline-block;
                    width: 4px;
                    height: 22px;
                    background: #3498db;
                    border-radius: 2px;
                }
                .modern-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 8px;
                }
                .modern-table thead th {
                    background: #f8f9fa;
                    color: #7f8c8d;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    padding: 10px 16px;
                    border: none;
                    border-bottom: none;
                }
                .modern-table thead th:first-child {
                    border-top-left-radius: 8px;
                    border-bottom-left-radius: 8px;
                }
                .modern-table thead th:last-child {
                    border-top-right-radius: 8px;
                    border-bottom-right-radius: 8px;
                }
                .modern-table tbody td {
                    background: #fff;
                    padding: 12px 16px;
                    border: none;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                    font-size: 14px;
                }
                .modern-table tbody tr {
                    border-radius: 8px;
                    transition: background 0.2s ease;
                }
                .modern-table tbody tr:hover td {
                    background: #f8f9fa;
                }
                .modern-table tbody td:first-child {
                    border-top-left-radius: 8px;
                    border-bottom-left-radius: 8px;
                    font-weight: 600;
                    color: #34495e;
                }
                .modern-table tbody td:last-child {
                    border-top-right-radius: 8px;
                    border-bottom-right-radius: 8px;
                }
                .employee-name {
                    font-weight: 600;
                    color: #2c3e50;
                }
                .employee-id {
                    color: #95a5a6;
                    font-weight: 400;
                    font-size: 13px;
                }
                .status-badge {
                    display: inline-block;
                    padding: 5px 14px;
                    border-radius: 20px;
                    color: #fff;
                    font-weight: 600;
                    font-size: 12px;
                }
                .status-approved {
                    background: linear-gradient(135deg, #27ae60, #2ecc71);
                }
                .status-rejected {
                    background: linear-gradient(135deg, #c0392b, #e74c3c);
                }
                .status-pending {
                    background: linear-gradient(135deg, #e67e22, #f39c12);
                }
                .action-btn {
                    display: inline-block;
                    padding: 6px 16px;
                    border-radius: 6px;
                    background: #3498db;
                    color: #fff;
                    font-size: 13px;
                    font-weight: 500;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    border: none;
                }
                .action-btn:hover {
                    background: #2980b9;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                }
                .empty-row td {
                    text-align: center;
                    color: #95a5a6;
                    font-style: italic;
                    padding: 30px;
                }

            .bottom-cards-row {
                display: flex;
                justify-content: center;
                padding: 20px 0;
            }
            .center-cards-wrapper {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                justify-content: center;
                max-width: 900px;
                width: 100%;
            }
            .bottom-stat-card {
                flex: 1 1 200px;
                text-align: center;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
                transition: transform 0.2s ease;
            }
            .bottom-stat-card:hover {
                transform: translateY(-3px);
            }
            .bottom-stat-card .stat-number {
                font-size: 36px;
                font-weight: 700;
            }
            .bottom-stat-card .stat-title {
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            </style>
    </head>
    <body>
        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <main class="mn-inner">
            <div class="row">
                <div class="col s12">
                    <div class="page-title">Tableau de Bord</div>
                </div>
            </div>
            
            <div class="row stats-cards-row">
                <!-- Total Employés -->
                <div class="col s12 m6 l3">
                    <div class="card dashboard-stat blue darken-1">
                        <div class="row m-0">
                            <div class="col s4 center-align">
                                <i class="material-icons stat-icon">people</i>
                            </div>
                            <div class="col s8 right-align">
                                <?php 
                                $sql = "SELECT id from tblemployees";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $empcount = $query->rowCount();
                                ?>
                                <div class="stat-number"><?php echo htmlentities($empcount); ?></div>
                                <div class="stat-title">Employés</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Services -->
                <div class="col s12 m6 l3">
                    <div class="card dashboard-stat teal darken-1">
                        <div class="row m-0">
                            <div class="col s4 center-align">
                                <i class="material-icons stat-icon">business</i>
                            </div>
                            <div class="col s8 right-align">
                                <?php 
                                $sql = "SELECT id from tbldepartments";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $dptcount = $query->rowCount();
                                ?>
                                <div class="stat-number"><?php echo htmlentities($dptcount); ?></div>
                                <div class="stat-title">Services</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Types de Congés -->
                <div class="col s12 m6 l3">
                    <div class="card dashboard-stat orange darken-1">
                        <div class="row m-0">
                            <div class="col s4 center-align">
                                <i class="material-icons stat-icon">event_note</i>
                            </div>
                            <div class="col s8 right-align">
                                <?php 
                                $sql = "SELECT id from tblleavetype";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $leavetypecount = $query->rowCount();
                                ?>
                                <div class="stat-number"><?php echo htmlentities($leavetypecount); ?></div>
                                <div class="stat-title">Types de Congé</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Congés en attente -->
                <div class="col s12 m6 l3">
                    <div class="card dashboard-stat red darken-1">
                        <div class="row m-0">
                            <div class="col s4 center-align">
                                <i class="material-icons stat-icon">warning</i>
                            </div>
                            <div class="col s8 right-align">
                                <?php 
                                $sql = "SELECT id from tblleaves WHERE Status=0";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $pendingleaves = $query->rowCount();
                                ?>
                                <div class="stat-number"><?php echo htmlentities($pendingleaves); ?></div>
                                <div class="stat-title">En attente</div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cartes des chiffres au centre -->
            <div class="row bottom-cards-row">
                <?php 
                $sqlPending = "SELECT COUNT(*) FROM tblleaves WHERE Status=0";
                $queryPending = $dbh->prepare($sqlPending);
                $queryPending->execute();
                $pendingCount = $queryPending->fetchColumn();

                $sqlApproved = "SELECT COUNT(*) FROM tblleaves WHERE Status=1";
                $queryApproved = $dbh->prepare($sqlApproved);
                $queryApproved->execute();
                $approvedCount = $queryApproved->fetchColumn();

                $sqlRejected = "SELECT COUNT(*) FROM tblleaves WHERE Status=2";
                $queryRejected = $dbh->prepare($sqlRejected);
                $queryRejected->execute();
                $rejectedCount = $queryRejected->fetchColumn();

                $sqlTotal = "SELECT COUNT(*) FROM tblleaves";
                $queryTotal = $dbh->prepare($sqlTotal);
                $queryTotal->execute();
                $totalCount = $queryTotal->fetchColumn();
                ?>
                <div class="col center-cards-wrapper">
                    <div class="card bottom-stat-card green darken-1">
                        <div class="card-content white-text">
                            <div class="stat-number"><?php echo htmlentities($approvedCount); ?></div>
                            <div class="stat-title">Approuvés</div>
                        </div>
                        </div>
                    <div class="card bottom-stat-card orange darken-1">
                        <div class="card-content white-text">
                            <div class="stat-number"><?php echo htmlentities($pendingCount); ?></div>
                            <div class="stat-title">En attente</div>
                        </div>
                    </div>
                    <div class="card bottom-stat-card red darken-1">
                        <div class="card-content white-text">
                            <div class="stat-number"><?php echo htmlentities($rejectedCount); ?></div>
                            <div class="stat-title">Refusés</div>
                        </div>
                    </div>
                    <div class="card bottom-stat-card blue darken-1">
                        <div class="card-content white-text">
                            <div class="stat-number"><?php echo htmlentities($totalCount); ?></div>
                            <div class="stat-title">Total demandes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau demandes de congé en bas de page -->
            <div class="row">
                <div class="col s12 m12 l12">
            <div class="table-modern-wrapper">
                <div class="table-modern-title">Dernières demandes de congé</div>
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="recent_leaves" class="display responsive-table table-data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employé</th>
                                    <th>Type de congé</th>
                                    <th>Date de demande</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sql = "SELECT tblleaves.id as lid, tblemployees.FirstName, tblemployees.LastName, tblemployees.EmpId, tblleaves.LeaveType, tblleaves.PostingDate, tblleaves.Status FROM tblleaves JOIN tblemployees ON tblleaves.empid=tblemployees.id ORDER BY lid DESC LIMIT 5";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $sn = 1;
                                if($query->rowCount() > 0) {
                                    foreach($results as $result) { ?>
                                        <tr>
                                            <td><?php echo $sn++; ?></td>
                                            <td class="employee-name"><?php echo htmlentities($result->FirstName." ".$result->LastName); ?> <span class="employee-id">(<?php echo htmlentities($result->EmpId); ?>)</span></td>
                                            <td><?php echo htmlentities($result->LeaveType); ?></td>
                                            <td><?php echo htmlentities(date("d/m/Y H:i", strtotime($result->PostingDate))); ?></td>
                                            <td>
                                                <?php 
                                                $stats = $result->Status;
                                                if($stats == 1) {
                                                    echo '<span style="color: green; font-weight: bold;">Approuvé</span>';
                                                } else if($stats == 2) {
                                                    echo '<span style="color: red; font-weight: bold;">Refusé</span>';
                                                } else {
                                                    echo '<span style="color: orange; font-weight: bold;">En attente</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>        <a href="leave-details.php?leaveid=<?php echo htmlentities($result->lid); ?>" class="action-btn" title="Détails"><i class="material-icons">visibility</i></a></td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr class="empty-row">
                                        <td colspan="6">Aucune demande récente</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </main>

        <script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
        <script src="../assets/plugins/materialize/js/materialize.min.js"></script>
        <script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
        <script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
        <script src="../assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="../assets/plugins/chart.js/chart.min.js"></script>
        <script src="../assets/js/alpha.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#recent_leaves').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json"
                    },
                    "pageLength": 10,
                    "paging": false,
                    "info": false
                });
            });
        </script>
    </body>
</html>
<?php } ?>