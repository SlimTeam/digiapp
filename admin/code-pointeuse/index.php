<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(strlen($_SESSION['alogin'])==0) {
    header('location:../index.php');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Gestion des Pointages ZKTeco - Quartz Gres</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">

        <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app">
        <?php include('../includes/header.php'); ?>
        <?php include('../includes/sidebar.php'); ?>
        <main class="content">
            <div class="container">
                <h1> Pointages ZKTeco</h1>
                <p class="subtitle">Entreprise : <strong>QUARTZ GRES</strong></p>
                <button id="btn-sync" class="btn btn-primary">🔄 Synchroniser</button>
                <button id="btn-export" class="btn btn-success">📥 Exporter</button>
                <button id="btn-print" class="btn btn-info">🖨️ Imprimer</button>
                <a href="manage.php" class="btn btn-warning">⚙️ Paramètres</a>
                <a href="dashboard.php" class="btn btn-primary">📊 Dashboard</a>

                <!-- Cartes d'indicateurs (KPIs) -->
                <div class="kpi-grid">
                    <div class="card">
                        <h3>Total Pointages</h3>
                        <p id="kpi-total" class="kpi-number">0</p>
                    </div>
                    <div class="card">
                        <h3>Derni&egrave;re Sync</h3>
                        <p id="kpi-sync" class="kpi-text">--:--</p>
                    </div>
                </div>

                <!-- Etat des pointeuses -->
                <div id="device-status" class="device-status-card">
                    Chargement de l'&eacute;tat des pointeuses...
                </div>

                <!-- Zone de Filtres -->
                <div class="filters-card">
                    <input type="text" id="search-name" placeholder="Rechercher par nom ou prénom...">
                    <div class="date-range">
                        <span class="date-label">Du</span>
                        <input type="date" id="filter-date-from">
                        <span class="date-label">Au</span>
                        <input type="date" id="filter-date-to">
                    </div>
                    <select id="filter-type">
                        <option value="">Tous les types</option>
                        <option value="Entrée">Entrée</option>
                        <option value="Sortie">Sortie</option>
                    </select>
                </div>

                <!-- Compteur de lignes -->
                <div id="row-count" class="row-count"></div>

                <!-- Tableau des Pointages -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employé (Nom & Prénom)</th>
                                <th>Département</th>
                                <th>Horodatage</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <tr><td colspan="4" class="loading">Chargement des pointages...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="app.js"></script>
</body>
</html>
