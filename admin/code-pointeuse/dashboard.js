document.addEventListener('DOMContentLoaded', () => {
    let kpiData = null;
    let charts = {};

    const dashboardLoading = document.getElementById('dashboard-loading');
    const dashboardContent = document.getElementById('dashboard-content');

    function renderChart(ctx, config) {
        if (charts[ctx]) {
            charts[ctx].destroy();
        }
        charts[ctx] = new Chart(document.getElementById(ctx), config);
    }

    function chartOptions(title) {
        return {
            responsive: true,
            plugins: {
                title: { display: true, text: title, font: { size: 14 } },
                legend: { position: 'top' },
            },
            scales: {
                y: { beginAtZero: true }
            }
        };
    }

    async function loadDashboard() {
        if (!dashboardLoading || !dashboardContent) return;

        try {
            const response = await fetch('dashboard-api.php');
            kpiData = await response.json();

            if (kpiData.status === 'error') {
                dashboardLoading.innerHTML = `<p style="color:red;">Erreur : ${kpiData.message}</p>`;
                return;
            }

            dashboardLoading.style.display = 'none';
            dashboardContent.style.display = 'block';
            renderDashboardKPIs();
            renderCharts();
            renderDepartmentTable();
            renderBurnoutRisk();
            renderRecommendations();
        } catch (error) {
            dashboardLoading.innerHTML = `<p style="color:red;">Erreur lors du chargement du tableau de bord.</p>`;
            console.error('Dashboard error:', error);
        }
    }

    function renderDashboardKPIs() {
        const k = kpiData.kpis;

        const presenceRateEl = document.getElementById('kpi-presence-rate');
        const absenceRateEl = document.getElementById('kpi-absence-rate');
        const overtimeHoursEl = document.getElementById('kpi-overtime-hours');
        const overtimeCostEl = document.getElementById('kpi-overtime-cost');
        const overtimePctEl = document.getElementById('kpi-overtime-pct');
        const anomalyRateEl = document.getElementById('kpi-anomaly-rate');
        const retardsEl = document.getElementById('kpi-retards');
        const earlyLeavesEl = document.getElementById('kpi-early-leaves');
        const legalEl = document.getElementById('kpi-legal-violations');

        if (presenceRateEl) {
            presenceRateEl.textContent = k.base_stats.presence_rate + '%';
            presenceRateEl.className = 'kpi-number ' + (k.base_stats.presence_rate >= 90 ? 'success' : k.base_stats.presence_rate >= 70 ? 'warning' : 'danger');
        }
        if (absenceRateEl) {
            absenceRateEl.textContent = k.base_stats.absenteeism_rate + '%';
            absenceRateEl.className = 'kpi-number ' + (k.base_stats.absenteeism_rate <= 5 ? 'success' : k.base_stats.absenteeism_rate <= 15 ? 'warning' : 'danger');
        }
        if (overtimeHoursEl) {
            overtimeHoursEl.textContent = k.overtime.total_hours.toFixed(1) + 'h';
            overtimeHoursEl.className = 'kpi-number ' + (k.overtime.total_hours > 20 ? 'danger' : k.overtime.total_hours > 5 ? 'warning' : 'success');
        }
        if (overtimeCostEl) overtimeCostEl.textContent = k.overtime.cost.toFixed(0) + ' TND';
        if (overtimePctEl) overtimePctEl.textContent = k.overtime.proportion + '%';
        if (anomalyRateEl) anomalyRateEl.textContent = k.anomalies.anomaly_rate + '%';
        if (retardsEl) retardsEl.textContent = k.punctuality.total_retards;
        if (earlyLeavesEl) earlyLeavesEl.textContent = k.punctuality.total_early_leaves;
        if (legalEl) {
            legalEl.textContent = (k.legal_compliance.rest_violations + k.legal_compliance.daily_max_violations + k.legal_compliance.weekly_rest_violations).toString();
            legalEl.className = 'kpi-number ' + ((k.legal_compliance.rest_violations + k.legal_compliance.daily_max_violations + k.legal_compliance.weekly_rest_violations) > 0 ? 'danger' : 'success');
        }

        const presentDaysEl = document.getElementById('kpi-present-days');
        const totalWorkdaysEl = document.getElementById('kpi-total-workdays');
        const absentDaysEl = document.getElementById('kpi-absent-days');
        if (presentDaysEl) presentDaysEl.textContent = k.base_stats.total_present_days;
        if (totalWorkdaysEl) totalWorkdaysEl.textContent = k.base_stats.total_working_days;
        if (absentDaysEl) absentDaysEl.textContent = k.base_stats.total_absent_days;

        const anomalyCountEl = document.getElementById('kpi-anomaly-count');
        const totalRecordsEl = document.getElementById('kpi-total-records');
        if (anomalyCountEl) anomalyCountEl.textContent = k.anomalies.total_anomalies;
        if (totalRecordsEl) totalRecordsEl.textContent = kpiData.total_records;

        document.getElementById('detail-overtime-hours').textContent = k.overtime.total_hours.toFixed(1) + 'h';
        document.getElementById('detail-overtime-cost').textContent = k.overtime.cost.toFixed(0) + ' TND';
        document.getElementById('detail-overtime-pct').textContent = k.overtime.proportion + '%';
        document.getElementById('detail-overtime-daily').textContent = k.overtime.avg_daily.toFixed(1) + 'h';
        document.getElementById('detail-anomaly-count').textContent = k.anomalies.total_anomalies;
        document.getElementById('detail-anomaly-rate').textContent = k.anomalies.anomaly_rate + '%';
        document.getElementById('detail-retards-count').textContent = k.punctuality.total_retards;
        document.getElementById('detail-earlyleaves-count').textContent = k.punctuality.total_early_leaves;
        document.getElementById('detail-rest-violations').textContent = k.legal_compliance.rest_violations;
        document.getElementById('detail-daily-max-violations').textContent = k.legal_compliance.daily_max_violations;
        document.getElementById('detail-weekly-rest-violations').textContent = k.legal_compliance.weekly_rest_violations;
        document.getElementById('detail-total-overtime').textContent = k.overtime.total_hours.toFixed(1) + 'h';
    }

    function renderCharts() {
        const k = kpiData.kpis;

        renderChart('chart-presence-absence', {
            type: 'bar',
            data: {
                labels: kpiData.daily_stats.map(d => d.day_name + ' ' + d.date.substring(5)),
                datasets: [
                    { label: 'Présents', data: kpiData.daily_stats.map(d => d.present_users), backgroundColor: '#10b981' },
                    { label: 'Absents', data: kpiData.daily_stats.map(d => d.absent_users), backgroundColor: '#ef4444' },
                ]
            },
            options: chartOptions('Présence / Absence par jour')
        });

        renderChart('chart-retards-departs', {
            type: 'bar',
            data: {
                labels: kpiData.daily_stats.map(d => d.day_name + ' ' + d.date.substring(5)),
                datasets: [
                    { label: 'Retards', data: kpiData.daily_stats.map(d => d.retards), backgroundColor: '#f59e0b' },
                    { label: 'Départs anticipés', data: kpiData.daily_stats.map(d => d.early_leaves), backgroundColor: '#3b82f6' },
                ]
            },
            options: chartOptions('Ponctualité par jour')
        });

        renderChart('chart-seasonality', {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sat', 'Dim'],
                datasets: [
                    { label: 'Retards', data: k.seasonality.map(s => s.retards), backgroundColor: '#f59e0b' },
                    { label: 'Absences', data: k.seasonality.map(s => s.absences), backgroundColor: '#ef4444' },
                ]
            },
            options: chartOptions('Saisonnalité par jour de la semaine')
        });

        const deptData = kpiData.department_stats;
        renderChart('chart-department-comparison', {
            type: 'bar',
            data: {
                labels: deptData.map(d => d.name),
                datasets: [
                    { label: 'Heures totales', data: deptData.map(d => d.total_hours.toFixed(1)), backgroundColor: '#2563eb' },
                    { label: 'Heures sup.', data: deptData.map(d => d.total_overtime.toFixed(1)), backgroundColor: '#f59e0b' },
                    { label: 'Retards', data: deptData.map(d => d.total_retards), backgroundColor: '#ef4444' },
                    { label: 'Anomalies', data: deptData.map(d => d.total_anomalies), backgroundColor: '#8b5cf6' },
                ]
            },
            options: chartOptions('Comparaison par service')
        });

        const dailyHours = kpiData.daily_stats.map(d => ({ date: d.date, hours: d.total_working_hours }));
        const avgHours = kpiData.config.standard_hours;
        renderChart('chart-planning-adherence', {
            type: 'bar',
            data: {
                labels: dailyHours.map(d => d.date.substring(5)),
                datasets: [
                    { label: 'Heures réelles', data: dailyHours.map(d => d.hours.toFixed(1)), backgroundColor: '#2563eb' },
                    { label: 'Heures théoriques', data: dailyHours.map(() => avgHours), backgroundColor: '#94a3b8', type: 'line', fill: false },
                ]
            },
            options: chartOptions('Adhérence au planning (heures/jour)')
        });

        const sortedUsers = [...kpiData.user_stats].sort((a, b) => (b.total_hours + b.overtime_hours + b.anomalies) - (a.total_hours + a.overtime_hours + a.anomalies));
        const topUsers = sortedUsers.slice(0, Math.min(15, sortedUsers.length));
        renderChart('chart-abs-employee-comparison', {
            type: 'bar',
            data: {
                labels: topUsers.map(u => u.name),
                datasets: [
                    { label: 'Heures travaillées', data: topUsers.map(u => u.total_hours.toFixed(1)), backgroundColor: '#2563eb' },
                    { label: 'Heures sup.', data: topUsers.map(u => u.overtime_hours.toFixed(1)), backgroundColor: '#f59e0b' },
                    { label: 'Anomalies', data: topUsers.map(u => u.anomalies), backgroundColor: '#ef4444' },
                    { label: 'Retards', data: topUsers.map(u => u.retards), backgroundColor: '#8b5cf6' },
                ]
            },
            options: {
                ...chartOptions('Absenteisme / Anomalies par employé'),
                indexAxis: 'y',
            }
        });
    }

    function renderDepartmentTable() {
        const tbody = document.getElementById('dept-table-body');
        if (!tbody) return;
        tbody.innerHTML = kpiData.department_stats.map(d => `
            <tr>
                <td><strong>${d.name}</strong></td>
                <td>${d.users}</td>
                <td>${d.total_hours.toFixed(1)}h</td>
                <td>${d.total_overtime.toFixed(1)}h</td>
                <td>${d.total_retards}</td>
                <td>${d.total_anomalies}</td>
            </tr>
        `).join('');
    }

    function renderBurnoutRisk() {
        const container = document.getElementById('burnout-risk-list');
        if (!container) return;
        const atRisk = kpiData.kpis.burnout_risk.at_risk_users;
        if (!atRisk || atRisk.length === 0) {
            container.innerHTML = '<p class="loading">Aucun collaborateur en risque identifié.</p>';
            return;
        }
        container.innerHTML = `
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Département</th>
                        <th>Heures totales</th>
                        <th>Heures sup.</th>
                        <th>Anomalies</th>
                    </tr>
                </thead>
                <tbody>
                    ${atRisk.map(u => `
                        <tr class="${u.total_hours > 150 ? 'risk-high' : 'risk-medium'}">
                            <td><strong>${u.name}</strong></td>
                            <td>${u.department}</td>
                            <td>${u.total_hours.toFixed(1)}h</td>
                            <td>${u.overtime_hours.toFixed(1)}h</td>
                            <td>${u.anomalies}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function renderRecommendations() {
        const container = document.getElementById('recommendations-list');
        if (!container) return;
        const recs = kpiData.recommendations;
        if (!recs || recs.length === 0) {
            container.innerHTML = '<p class="loading">Aucune recommandation.</p>';
            return;
        }
        container.innerHTML = recs.map(r => `
            <div class="recommendation-card priority-${r.priority}">
                <div class="rec-header">
                    <span class="rec-category">${r.category}</span>
                    <span class="rec-priority priority-${r.priority}">${r.priority === 'high' ? 'Urgent' : r.priority === 'medium' ? 'Moyenne' : 'Faible'}</span>
                </div>
                <p class="rec-text">${r.text}</p>
            </div>
        `).join('');
    }

    loadDashboard();
});
