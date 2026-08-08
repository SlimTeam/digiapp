<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytique - Gestion des Pointages ZKTeco</title>
    <link href="../style.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="app">
        <?php include('../includes/header.php'); ?>
        <?php include('../includes/sidebar.php'); ?>
        <main class="content">
            <div class="container">
                <h1>📊 Dashboard Analytique</h1>
                <p class="subtitle">Entreprise : <strong>QUARTZ GRES</strong></p>
                <div class="header-actions">
                    <a href="index.php" class="btn">← Retour au tableau</a>
                </div>

                <div id="dashboard-loading" class="loading">Chargement du tableau de bord...</div>
                <div id="dashboard-content" style="display:none;">

                    <!-- Section 1: KPIs de base -->
                    <h2 class="dashboard-section-title">1. Indicateurs de Base et Statistiques Descriptives</h2>
                    <div class="kpi-grid">
                        <div class="card">
                            <h3>Taux de présence</h3>
                            <p id="kpi-presence-rate" class="kpi-number">0%</p>
                            <div class="kpi-sub">Jours présents: <span id="kpi-present-days">0</span> / <span id="kpi-total-workdays">0</span></div>
                        </div>
                        <div class="card">
                            <h3>Taux d'absentéisme</h3>
                            <p id="kpi-absence-rate" class="kpi-number danger">0%</p>
                            <div class="kpi-sub">Jours absents: <span id="kpi-absent-days">0</span></div>
                        </div>
                        <div class="card">
                            <h3>Heures supplémentaires</h3>
                            <p id="kpi-overtime-hours" class="kpi-number warning">0h</p>
                            <div class="kpi-sub">Coût: <span id="kpi-overtime-cost">0</span> TND | <span id="kpi-overtime-pct">0</span>% du volume horaire</div>
                        </div>
                        <div class="card">
                            <h3>Anomalies de pointage</h3>
                            <p id="kpi-anomaly-rate" class="kpi-number">0%</p>
                            <div class="kpi-sub"><span id="kpi-anomaly-count">0</span> anomalies sur <span id="kpi-total-records">0</span> pointages</div>
                        </div>
                        <div class="card">
                            <h3>Retards / Départs anticipés</h3>
                            <p id="kpi-punctuality" class="kpi-number">0</p>
                            <div class="kpi-sub">Retards: <span id="kpi-retards">0</span> | Départs anticipés: <span id="kpi-early-leaves">0</span></div>
                        </div>
                        <div class="card">
                            <h3>Violations légales</h3>
                            <p id="kpi-legal-violations" class="kpi-number danger">0</p>
                            <div class="kpi-sub">Repos (&lt; 11h): <span id="kpi-rest-violations">0</span> | Max journalier: <span id="kpi-daily-max-violations">0</span></div>
                        </div>
                    </div>

                    <!-- Sous-section 1.1: Absentéisme -->
                    <div class="dashboard-sub-section">
                        <h3>Taux d'absentéisme et de présence</h3>
                        <p class="text-sm">Ratio entre les jours de présence effectifs et les jours ouvrables théoriques.</p>
                        <div class="chart-row">
                            <canvas id="chart-presence-absence"></canvas>
                        </div>
                    </div>

                    <!-- Sous-section 1.2: Heures supplémentaires -->
                    <div class="dashboard-sub-section">
                        <h3>Volume et coût des heures supplémentaires</h3>
                        <p class="text-sm">Mesure du nombre total d'heures majorées, leur proportion et leur impact financier.</p>
                        <div class="kpi-grid">
                            <div class="card card-sm">
                                <h4>Total HE</h4>
                                <p class="kpi-value" id="detail-overtime-hours">0h</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Coût total</h4>
                                <p class="kpi-value" id="detail-overtime-cost">0 TND</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Proportion</h4>
                                <p class="kpi-value" id="detail-overtime-pct">0%</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Moyenne/jour</h4>
                                <p class="kpi-value" id="detail-overtime-daily">0h</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sous-section 1.3: Ponctualité -->
                    <div class="dashboard-sub-section">
                        <h3>Ponctualité et régularité</h3>
                        <p class="text-sm">Comptage des retards, départs anticipés et durée moyenne des dépassements.</p>
                        <div class="chart-row">
                            <canvas id="chart-retards-departs"></canvas>
                        </div>
                    </div>

                    <!-- Sous-section 1.4: Anomalies -->
                    <div class="dashboard-sub-section">
                        <h3>Taux d'anomalies de pointage</h3>
                        <p class="text-sm">Fréquence des oublis de pointage (entrées/sorties non renseignées).</p>
                        <div class="kpi-grid">
                            <div class="card card-sm">
                                <h4>Anomalies totales</h4>
                                <p class="kpi-value" id="detail-anomaly-count">0</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Taux d'anomalie</h4>
                                <p class="kpi-value" id="detail-anomaly-rate">0%</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Retards</h4>
                                <p class="kpi-value" id="detail-retards-count">0</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Départs anticipés</h4>
                                <p class="kpi-value" id="detail-earlyleaves-count">0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Analyses Temporelles -->
                    <h2 class="dashboard-section-title">2. Analyses Temporelles et Comportementales</h2>

                    <!-- Sous-section 2.1: Saisonnalité -->
                    <div class="dashboard-sub-section">
                        <h3>Cartographie de la saisonnalité et des récurrences</h3>
                        <p class="text-sm">Identification des pics d'absences ou de retards selon les jours et mois.</p>
                        <div class="chart-row">
                            <canvas id="chart-seasonality"></canvas>
                        </div>
                    </div>

                    <!-- Sous-section 2.2: Comparaison par service -->
                    <div class="dashboard-sub-section">
                        <h3>Analyse comparative par service et management</h3>
                        <p class="text-sm">Comparaison des taux de présence, retards et HE entre départements.</p>
                        <div class="chart-row">
                            <canvas id="chart-department-comparison"></canvas>
                        </div>
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Département</th>
                                    <th>Employés</th>
                                    <th>Heures totales</th>
                                    <th>HE totales</th>
                                    <th>Retards</th>
                                    <th>Anomalies</th>
                                </tr>
                            </thead>
                            <tbody id="dept-table-body"></tbody>
                        </table>
                    </div>

                    <!-- Sous-section 2.3: Adhérence au planning -->
                    <div class="dashboard-sub-section">
                        <h3>Adhérence au planning (Taux de conformité)</h3>
                        <p class="text-sm">Écart entre les horaires théoriques planifiés et les horaires réels d'exécution.</p>
                        <div class="chart-row">
                            <canvas id="chart-planning-adherence"></canvas>
                        </div>
                    </div>

                    <!-- Section 3: Risques et Conformité -->
                    <h2 class="dashboard-section-title">3. Analyses Avancées de Risques et de Conformité</h2>

                    <!-- Sous-section 3.1: Cadre légal -->
                    <div class="dashboard-sub-section">
                        <h3>Respect du cadre légal et conventionnel</h3>
                        <p class="text-sm">Contrôle des repos quotidiens (11h), repos hebdomadaires, durée maximale et contingent d'HE.</p>
                        <div class="kpi-grid">
                            <div class="card card-sm">
                                <h4>Violations repos ( &lt; 11h )</h4>
                                <p class="kpi-value" id="detail-rest-violations">0</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Durée max journalière ( &gt; 10h )</h4>
                                <p class="kpi-value" id="detail-daily-max-violations">0</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Repos hebdo manquant</h4>
                                <p class="kpi-value" id="detail-weekly-rest-violations">0</p>
                            </div>
                            <div class="card card-sm">
                                <h4>Total heures HE</h4>
                                <p class="kpi-value" id="detail-total-overtime">0h</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sous-section 3.2: Risques psychosociaux -->
                    <div class="dashboard-sub-section">
                        <h3>Détection des risques psychosociaux et du surmenage</h3>
                        <p class="text-sm">Repérage des collaborateurs avec un volume d'heures excessif sans repos suffisant.</p>
                        <div id="burnout-risk-list">
                            <p class="loading">Aucun risque détecté.</p>
                        </div>
                    </div>

                    <!-- Sous-section 3.3: Corrélation Absentéisme/Turnover -->
                    <div class="dashboard-sub-section">
                        <h3>Corrélation Absentéisme / Turnover</h3>
                        <p class="text-sm">Croisement des données de ponctualité/absence avec le taux de démission.</p>
                        <div class="chart-row">
                            <canvas id="chart-abs-employee-comparison"></canvas>
                        </div>
                        <p class="text-sm">Note : Le taux de turnover requiert des données RH externes. Ce graphique compare l'absentéisme par employé.</p>
                    </div>

                    <!-- Section 4: Plan d'Action -->
                    <h2 class="dashboard-section-title">4. Plan d'Action pour Optimiser l'Organisation du Travail</h2>

                    <div class="dashboard-sub-section">
                        <h3>Recommandations d'optimisation</h3>
                        <div id="recommendations-list"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>
