document.addEventListener('DOMContentLoaded', () => {
    let rawData = [];

    const tableBody = document.getElementById('table-body');
    const kpiTotal = document.getElementById('kpi-total');
    const kpiSync = document.getElementById('kpi-sync');
    const searchInput = document.getElementById('search-name');
    const dateInputFrom = document.getElementById('filter-date-from');
    const dateInputTo = document.getElementById('filter-date-to');
    const typeSelect = document.getElementById('filter-type');
    const btnSync = document.getElementById('btn-sync');
    const btnExport = document.getElementById('btn-export');
    const btnPrint = document.getElementById('btn-print');
    const statusContainer = document.getElementById('device-status');

    async function loadData() {
        try {
            const response = await fetch('api.php');
            const result = await response.json();

            if (result.status === 'error') {
                tableBody.innerHTML = `<tr><td colspan="4" class="loading" style="color:red;">Erreur: ${result.message}</td></tr>`;
                return;
            }

            rawData = result.data || [];
            if (kpiTotal) kpiTotal.textContent = result.count || 0;
            if (kpiSync) kpiSync.textContent = result.last_sync || 'Inconnue';
            filterData();
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="4" class="loading" style="color:red;">Erreur lors de la connexion au serveur.</td></tr>`;
        }
    }

    async function loadDeviceStatus() {
        if (!statusContainer) return;

        try {
            const response = await fetch('status.php');
            const result = await response.json();

            if (result.status === 'error') {
                statusContainer.innerHTML = `<div class="status-error">Erreur : ${result.message}</div>`;
                return;
            }

            const connectedDevices = result.devices.filter(device => device.reachable).length;

            statusContainer.innerHTML = result.devices.length > 0
                ? `${result.devices.map(device => `
                    <div class="device-card ${device.reachable ? 'device-up' : 'device-down'}">
                        <div class="device-card-header">
                            <div class="device-info">
                                <span class="device-name">${device.name || ''}</span>
                                <span class="device-ip">${device.ip || ''}</span>
                            </div>
                            <span class="device-state">${device.reachable ? 'Connectée' : 'Inaccessible'}</span>
                        </div>
                    </div>
                `).join('')}`
                : '<div class="device-status-summary">Aucune pointeuse accessible actuellement.</div>';
        } catch (error) {
            statusContainer.innerHTML = `<div class="status-error">Impossible de récupérer l'état des pointeuses.</div>`;
            console.error('Erreur device status:', error);
        }
    }

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const dateFrom = dateInputFrom ? dateInputFrom.value : '';
        const dateTo = dateInputTo ? dateInputTo.value : '';
        const selectedType = typeSelect.value;

        return rawData.filter(item => {
            const matchesSearch = !query || (item.name && item.name.toLowerCase().includes(query));
            const checkDate = item.check_time ? item.check_time.substring(0, 10) : '';
            const matchesDateFrom = !dateFrom || checkDate >= dateFrom;
            const matchesDateTo = !dateTo || checkDate <= dateTo;
            const matchesType = !selectedType || item.type === selectedType;

            return matchesSearch && matchesDateFrom && matchesDateTo && matchesType;
        });
    }

    function filterData() {
        const filtered = applyFilters();
        renderTable(filtered);
        updateRowCount(filtered.length, rawData.length);
    }

    function updateRowCount(visible, total) {
        const rowCount = document.getElementById('row-count');
        if (rowCount) {
            rowCount.textContent = `${visible} ligne${visible !== 1 ? 's' : ''} affichée${visible !== 1 ? 's' : ''} / ${total}`;
        }
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="loading">Aucun pointage trouvé.</td></tr>`;
            return;
        }
        tableBody.innerHTML = data.map(item => `
            <tr>
                <td><strong>${item.name || 'Employé inconnu'}</strong></td>
                <td>${item.department}</td>
                <td>${item.check_time}</td>
                <td class="tag-${item.type.toLowerCase()}">${item.type}</td>
            </tr>
        `).join('');
    }

    function exportCSV() {
        const filtered = applyFilters();
        const headers = ['Employé', 'Département', 'Horodatage', 'Type'];
        const rows = filtered.map(item => [
            item.name || '',
            item.department || '',
            item.check_time || '',
            item.type || '',
        ]);

        const csvContent = [
            headers.join(';'),
            ...rows.map(row =>
                row.map(cell =>
                    '"' + String(cell).replace(/"/g, '""') + '"'
                ).join(';')
            )
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const dateLabel = dateInputFrom && dateInputFrom.value && dateInputTo && dateInputTo.value
            ? `_${dateInputFrom.value}_${dateInputTo.value}`
            : '_all';
        a.href = url;
        a.download = `pointages${dateLabel}.csv`;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function printReport() {
        const filtered = applyFilters();
        const printWindow = window.open('', '_blank');
        const dateLabel = dateInputFrom && dateInputFrom.value && dateInputTo && dateInputTo.value
            ? `${dateInputFrom.value} au ${dateInputTo.value}`
            : 'Toutes les dates';

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Pointages - Quartz Gres</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #1e293b; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
                    th { background-color: #f1f5f9; font-weight: 600; }
                    tr:nth-child(even) { background-color: #f8fafc; }
                    .meta { color: #64748b; font-size: 0.9em; margin-bottom: 10px; }
                </style>
            </head>
            <body>
                <h1>Pointages ZKTeco - Quartz Gres</h1>
                <div class="meta">Période : ${dateLabel} | Nombre de lignes : ${filtered.length}</div>
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Horodatage</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(item => `
                            <tr>
                                <td><strong>${item.name || ''}</strong></td>
                                <td>${item.department || ''}</td>
                                <td>${item.check_time || ''}</td>
                                <td>${item.type || ''}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <script>
                    window.onload = function() { window.print(); };
                </script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    if (searchInput) searchInput.addEventListener('input', filterData);
    if (dateInputFrom) dateInputFrom.addEventListener('change', filterData);
    if (dateInputTo) dateInputTo.addEventListener('change', filterData);
    if (typeSelect) typeSelect.addEventListener('change', filterData);

    if (btnSync) {
        btnSync.addEventListener('click', async () => {
            btnSync.disabled = true;
            btnSync.textContent = 'Synchronisation...';
            try {
                const syncResponse = await fetch('sync.php');
                const syncResult = await syncResponse.json();

                if (syncResult.status === 'error') {
                    throw new Error(syncResult.message || 'Erreur de synchronisation.');
                }

                await loadData();
                await loadDeviceStatus();
            } catch (err) {
                tableBody.innerHTML = `<tr><td colspan="4" class="loading" style="color:red;">Erreur de synchronisation : ${err.message}</td></tr>`;
                console.error('Erreur de synchronisation:', err);
            } finally {
                btnSync.disabled = false;
                btnSync.textContent = 'Synchroniser';
            }
        });
    }

    if (btnExport) {
        btnExport.addEventListener('click', exportCSV);
    }

    if (btnPrint) {
        btnPrint.addEventListener('click', printReport);
    }

    loadData();
    loadDeviceStatus();
});
