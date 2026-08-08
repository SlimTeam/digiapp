<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$jsonFile = __DIR__ . '/data.json';

if (!file_exists($jsonFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Fichier data.json introuvable.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents($jsonFile);
$data = json_decode($content, true);

if (!$data || !isset($data['data'])) {
    echo json_encode(['status' => 'error', 'message' => 'Donnees invalides dans data.json.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$records = $data['data'];
usort($records, function($a, $b) {
    return strtotime($a['check_time']) - strtotime($b['check_time']);
});

$CONFIG = [
    'standard_hours'        => 7.5,
    'overtime_threshold'    => 7.5,
    'lateness_threshold'    => 8.5,
    'early_leave_threshold' => 17.0,
    'hourly_rate'           => 25.0,
    'daily_max_hours'       => 10,
    'weekly_max_hours'      => 48,
    'min_rest_hours'        => 11,
    'workdays'              => [1, 2, 3, 4, 5],
    'lunch_break_hours'     => 1.0,
];

$users = [];
$allDates = [];

foreach ($records as $r) {
    $uid = $r['user_id'];
    $dt = new DateTime($r['check_time'], new DateTimeZone(date_default_timezone_get()));
    $dateStr = $dt->format('Y-m-d');

    if (!isset($users[$uid])) {
        $users[$uid] = [
            'name'       => $r['name'],
            'department' => $r['department'] ?? 'Général',
            'dates'      => [],
        ];
    }
    if (!isset($users[$uid]['dates'][$dateStr])) {
        $users[$uid]['dates'][$dateStr] = ['entries' => [], 'exits' => []];
    }

    $type = strtolower($r['type'] ?? '');
    if ($type === 'sortie' || $type === 'exit' || $type === 'checkout') {
        $users[$uid]['dates'][$dateStr]['exits'][] = $dt;
    } else {
        $users[$uid]['dates'][$dateStr]['entries'][] = $dt;
    }

    if (!in_array($dateStr, $allDates)) {
        $allDates[] = $dateStr;
    }
}

usort($allDates, function($a, $b) { return strcmp($a, $b); });

$workdayDates = [];
foreach ($allDates as $d) {
    $dt = new DateTime($d);
    if (in_array((int)$dt->format('N'), $CONFIG['workdays'])) {
        $workdayDates[] = $d;
    }
}
sort($workdayDates);

$dailyStats = [];
$userDailyHours = [];
$userTotalHours = [];
$userOvertimeHours = [];
$userRetards = [];
$userEarlyLeaves = [];
$userAnomalies = [];
$totalAnomalies = 0;
$totalRetards = 0;
$totalEarlyLeaves = 0;
$allWorkingHours = [];
$allOvertimeHours = [];
$retardsByDayOfWeek = array_fill(1, 7, 0);
$absencesByDayOfWeek = array_fill(1, 7, 0);

foreach ($workdayDates as $dateStr) {
    $dt = new DateTime($dateStr);
    $dayOfWeek = (int)$dt->format('N');
    $monthKey = $dt->format('Y-m');

    $dailyStats[$dateStr] = [
        'date'             => $dateStr,
        'date_label'       => $dt->format('d/m'),
        'day_name'         => ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'][$dayOfWeek % 7],
        'present_users'    => 0,
        'absent_users'     => 0,
        'total_working_hours' => 0,
        'total_overtime'   => 0,
        'retards'          => 0,
        'early_leaves'     => 0,
        'anomalies'        => 0,
    ];

    $presentUsers = [];
    foreach ($users as $uid => $userInfo) {
        if (!isset($userInfo['dates'][$dateStr])) continue;

        $entries = $userInfo['dates'][$dateStr]['entries'];
        $exits = $userInfo['dates'][$dateStr]['exits'];

        if (count($entries) === 0 && count($exits) === 0) continue;

        $presentUsers[] = $uid;
        $dailyStats[$dateStr]['present_users']++;

        if (count($entries) === 0 && count($exits) > 0) {
            $exits = [];
        }

        $inferredExits = [];
        if (count($exits) === 0 && count($entries) >= 2) {
            $lastEntry = array_pop($entries);
            $inferredExits[] = $lastEntry;
            $remainingEntries = $entries;
        } else {
            $remainingEntries = $entries;
        }

        if (!empty($remainingEntries)) {
            $firstEntry = min($remainingEntries);
            $entryHour = (int)$firstEntry->format('G') + (int)$firstEntry->format('i') / 60;
            if ($entryHour > $CONFIG['lateness_threshold']) {
                $totalRetards++;
                $userRetards[$uid] = ($userRetards[$uid] ?? 0) + 1;
                $dailyStats[$dateStr]['retards']++;
                $retardsByDayOfWeek[$dayOfWeek]++;
            }
        }

        $allExits = array_merge($exits, $inferredExits);
        if (!empty($exits)) {
            $lastActualExit = max($exits);
            $exitHour = (int)$lastActualExit->format('G') + (int)$lastActualExit->format('i') / 60;
            if ($exitHour < $CONFIG['early_leave_threshold']) {
                $totalEarlyLeaves++;
                $userEarlyLeaves[$uid] = ($userEarlyLeaves[$uid] ?? 0) + 1;
                $dailyStats[$dateStr]['early_leaves']++;
            }
        }

        if (count($entries) > 0 && !empty($allExits)) {
            $firstEntry = min($entries);
            $lastExit = max($allExits);
            $diff = $lastExit->getTimestamp() - $firstEntry->getTimestamp();
            $workingHours = max(0, ($diff / 3600) - $CONFIG['lunch_break_hours']);

            $allWorkingHours[] = $workingHours;
            $dailyStats[$dateStr]['total_working_hours'] += $workingHours;
            $userTotalHours[$uid] = ($userTotalHours[$uid] ?? 0) + $workingHours;
            $userDailyHours[$uid][$dateStr] = $workingHours;

            $overtime = max(0, $workingHours - $CONFIG['overtime_threshold']);
            if ($overtime > 0) {
                $allOvertimeHours[] = $overtime;
                $dailyStats[$dateStr]['total_overtime'] += $overtime;
                $userOvertimeHours[$uid] = ($userOvertimeHours[$uid] ?? 0) + $overtime;
            }
        }

        $anomalies = 0;
        if (count($entries) > 0 && count($exits) > 0) {
            $anomalies = abs(count($entries) - count($exits));
        } elseif (count($entries) > 0) {
            $anomalies = count($entries) > 1 ? (count($entries) - 2) : 0;
        }

        $totalAnomalies += $anomalies;
        $userAnomalies[$uid] = ($userAnomalies[$uid] ?? 0) + $anomalies;
        $dailyStats[$dateStr]['anomalies'] += $anomalies;
    }

    $dailyStats[$dateStr]['absent_users'] = count($users) - $dailyStats[$dateStr]['present_users'];
    $absencesByDayOfWeek[$dayOfWeek] += $dailyStats[$dateStr]['absent_users'];
}

$totalRecords = count($records);
$totalUsers = count($users);
$totalWorkdays = count($workdayDates);
$totalPresentDays = 0;
$totalAbsentDays = 0;

foreach ($dailyStats as $ds) {
    $totalPresentDays += $ds['present_users'];
    $totalAbsentDays += $ds['absent_users'];
}

$totalPossibleAttendances = $totalUsers * $totalWorkdays;
$presenceRate = $totalPossibleAttendances > 0 ? round($totalPresentDays / $totalPossibleAttendances * 100, 1) : 0;
$absenteeismRate = round(100 - $presenceRate, 1);

$totalOvertimeHours = array_sum($allOvertimeHours);
$overtimeCost = round($totalOvertimeHours * $CONFIG['hourly_rate'], 2);
$totalWorkedHours = array_sum($allWorkingHours);
$overtimeProportion = $totalWorkedHours > 0 ? round($totalOvertimeHours / $totalWorkedHours * 100, 1) : 0;
$avgDailyOvertime = $totalWorkdays > 0 ? round($totalOvertimeHours / $totalWorkdays, 1) : 0;

$anomalyRate = $totalRecords > 0 ? round($totalAnomalies / $totalRecords * 100, 1) : 0;

$legalViolations = ['rest_violations' => 0, 'daily_max_violations' => 0, 'weekly_rest_violations' => 0];
$atRiskUsers = [];

foreach ($users as $uid => $userInfo) {
    $prevDate = null;
    $consecutiveDays = 0;
    $maxConsecutive = 0;

    foreach ($workdayDates as $wd) {
        $hasRecord = isset($userInfo['dates'][$wd]) &&
            (count($userInfo['dates'][$wd]['entries']) > 0 || count($userInfo['dates'][$wd]['exits']) > 0);

        if ($hasRecord) {
            $consecutiveDays++;
            if ($consecutiveDays > $maxConsecutive) $maxConsecutive = $consecutiveDays;

            $hours = $userDailyHours[$uid][$wd] ?? 0;
            if ($hours > $CONFIG['daily_max_hours']) {
                $legalViolations['daily_max_violations']++;
            }

            if ($prevDate !== null) {
                $prevDt = new DateTime($prevDate . ' 23:59:59');
                $entries = $userInfo['dates'][$wd]['entries'] ?? [];
                if (!empty($entries)) {
                    $currEntry = min($entries);
                    $restHours = ($currEntry->getTimestamp() - $prevDt->getTimestamp()) / 3600;
                    if ($restHours < $CONFIG['min_rest_hours']) {
                        $legalViolations['rest_violations']++;
                    }
                }
            }
            $prevDate = $wd;
        } else {
            $consecutiveDays = 0;
        }
    }

    if ($maxConsecutive > 6) {
        $legalViolations['weekly_rest_violations'] += ($maxConsecutive - 6);
    }

    $overtimeTotal = $userOvertimeHours[$uid] ?? 0;
    $hoursTotal = $userTotalHours[$uid] ?? 0;
    $anomaliesTotal = $userAnomalies[$uid] ?? 0;

    $riskScore = 0;
    if ($overtimeTotal > 15) $riskScore += 2;
    if ($hoursTotal > 0 && ($hoursTotal / max(1, $totalWorkdays)) > 10) $riskScore += 2;
    if ($anomaliesTotal > 50) $riskScore += 1;
    if ($userRetards[$uid] ?? 0 > 10) $riskScore += 1;

    if ($riskScore >= 2) {
        $atRiskUsers[] = [
            'user_id' => $uid,
            'name' => $userInfo['name'],
            'department' => $userInfo['department'],
            'total_hours' => round($hoursTotal, 1),
            'overtime_hours' => round($overtimeTotal, 1),
            'retards' => $userRetards[$uid] ?? 0,
            'anomalies' => $anomaliesTotal,
            'risk_score' => $riskScore,
        ];
    }
}

usort($atRiskUsers, function($a, $b) { return $b['risk_score'] - $a['risk_score']; });

$departmentStats = [];
foreach ($users as $uid => $userInfo) {
    $dept = $userInfo['department'];
    if (!isset($departmentStats[$dept])) {
        $departmentStats[$dept] = [
            'name' => $dept,
            'users' => 0,
            'total_hours' => 0,
            'total_overtime' => 0,
            'total_retards' => 0,
            'total_early_leaves' => 0,
            'total_anomalies' => 0,
            'presence_days' => 0,
            'absence_days' => 0,
        ];
    }
    $departmentStats[$dept]['users']++;
    $departmentStats[$dept]['total_hours'] += $userTotalHours[$uid] ?? 0;
    $departmentStats[$dept]['total_overtime'] += $userOvertimeHours[$uid] ?? 0;
    $departmentStats[$dept]['total_retards'] += $userRetards[$uid] ?? 0;
    $departmentStats[$dept]['total_early_leaves'] += $userEarlyLeaves[$uid] ?? 0;
    $departmentStats[$dept]['total_anomalies'] += $userAnomalies[$uid] ?? 0;

    $workdaysPresent = 0;
    foreach ($workdayDates as $wd) {
        if (isset($userInfo['dates'][$wd]) &&
            (count($userInfo['dates'][$wd]['entries']) > 0 || count($userInfo['dates'][$wd]['exits']) > 0)) {
            $workdaysPresent++;
        }
    }
    $departmentStats[$dept]['presence_days'] += $workdaysPresent;
    $departmentStats[$dept]['absence_days'] += max(0, $totalWorkdays - $workdaysPresent);
}

$userStats = [];
foreach ($users as $uid => $userInfo) {
    $workdaysPresent = 0;
    foreach ($workdayDates as $wd) {
        if (isset($userInfo['dates'][$wd]) &&
            (count($userInfo['dates'][$wd]['entries']) > 0 || count($userInfo['dates'][$wd]['exits']) > 0)) {
            $workdaysPresent++;
        }
    }
    $userStats[] = [
        'user_id' => $uid,
        'name' => $userInfo['name'],
        'department' => $userInfo['department'],
        'total_hours' => round($userTotalHours[$uid] ?? 0, 1),
        'overtime_hours' => round($userOvertimeHours[$uid] ?? 0, 1),
        'retards' => $userRetards[$uid] ?? 0,
        'early_leaves' => $userEarlyLeaves[$uid] ?? 0,
        'anomalies' => $userAnomalies[$uid] ?? 0,
        'workdays_present' => $workdaysPresent,
        'workdays_absent' => max(0, $totalWorkdays - $workdaysPresent),
    ];
}
usort($userStats, function($a, $b) {
    return ($b['total_hours'] + $b['overtime_hours'] + $b['anomalies'])
         - ($a['total_hours'] + $a['overtime_hours'] + $a['anomalies']);
});

$recommendations = [];

if ($absenteeismRate > 10) {
    $recommendations[] = [
        'category' => 'Absentéisme',
        'priority' => 'high',
        'text' => "Le taux d'absentéisme est de {$absenteeismRate}%, supérieur à 10%. Mettez en place un suivi individualisé des absences et un entretien d'ancrage.",
    ];
}

if ($overtimeProportion > 15) {
    $recommendations[] = [
        'category' => 'Heures supplémentaires',
        'priority' => 'high',
        'text' => "Les heures supplémentaires représentent {$overtimeProportion}% du volume horaire total (coût: {$overtimeCost} TND). Réévaluez le dimensionnement des équipes.",
    ];
}

if ($overtimeProportion > 5) {
    $recommendations[] = [
        'category' => 'Heures supplémentaires',
        'priority' => 'medium',
        'text' => "Les heures supplémentaires représentent {$overtimeProportion}% du volume horaire total. Considérez un réaménagement du planning.",
    ];
}

if ($anomalyRate > 15) {
    $recommendations[] = [
        'category' => 'Anomalies de pointage',
        'priority' => 'medium',
        'text' => "Le taux d'anomalies est de {$anomalyRate}%. Vérifiez l'ergonomie des pointeuses et formez les équipes.",
    ];
}

if ($legalViolations['rest_violations'] > 0) {
    $recommendations[] = [
        'category' => 'Conformité légale',
        'priority' => 'high',
        'text' => "{$legalViolations['rest_violations']} violation(s) de repos quotidien (< 11h). Action corrective immédiate requise.",
    ];
}

if ($legalViolations['daily_max_violations'] > 0) {
    $recommendations[] = [
        'category' => 'Conformité légale',
        'priority' => 'high',
        'text' => "{$legalViolations['daily_max_violations']} violation(s) de durée maximale journalière (> 10h). Vérifiez la planification.",
    ];
}

if (count($atRiskUsers) > 0) {
    $recommendations[] = [
        'category' => 'Risque psychosocial',
        'priority' => 'high',
        'text' => count($atRiskUsers) . " collaborateur(s) présentent un risque de surmenage. Entretiens individuels à planifier.",
    ];
}

if (count($recommendations) === 0) {
    $recommendations[] = [
        'category' => 'Tout est OK',
        'priority' => 'low',
        'text' => "Aucune anomalie majeure détectée. Continuez le suivi régulier.",
    ];
}

$seasonalityData = [];
$dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
for ($i = 1; $i <= 7; $i++) {
    $dayIdx = ($i - 1) % 7;
    $seasonalityData[] = [
        'day' => $dayLabels[$dayIdx],
        'retards' => $retardsByDayOfWeek[$i],
        'absences' => $absencesByDayOfWeek[$i],
    ];
}

$departmentChartData = [];
foreach ($departmentStats as $dept) {
    $possible = $dept['users'] * $totalWorkdays;
    $departmentChartData[] = [
        'name' => $dept['name'],
        'users' => $dept['users'],
        'total_hours' => round($dept['total_hours'], 1),
        'total_overtime' => round($dept['total_overtime'], 1),
        'total_retards' => $dept['total_retards'],
        'total_early_leaves' => $dept['total_early_leaves'],
        'total_anomalies' => $dept['total_anomalies'],
        'presence_rate' => $possible > 0 ? round($dept['presence_days'] / $possible * 100, 1) : 0,
    ];
}

$dailyChartData = [];
foreach ($dailyStats as $ds) {
    $dailyChartData[] = [
        'date' => $ds['date'],
        'date_label' => $ds['date_label'],
        'day_name' => $ds['day_name'],
        'present' => $ds['present_users'],
        'absent' => $ds['absent_users'],
        'working_hours' => round($ds['total_working_hours'], 1),
        'overtime' => round($ds['total_overtime'], 1),
        'retards' => $ds['retards'],
        'early_leaves' => $ds['early_leaves'],
        'anomalies' => $ds['anomalies'],
    ];
}

echo json_encode([
    'status' => 'success',
    'last_sync' => $data['last_sync'] ?? '',
    'total_records' => $totalRecords,
    'total_users' => $totalUsers,
    'date_range' => [
        'start' => $allDates[0] ?? null,
        'end' => $allDates[count($allDates) - 1] ?? null,
    ],
    'config' => $CONFIG,
    'kpis' => [
        'base_stats' => [
            'presence_rate' => $presenceRate,
            'absenteeism_rate' => $absenteeismRate,
            'total_present_days' => $totalPresentDays,
            'total_absent_days' => $totalAbsentDays,
            'total_working_days' => $totalWorkdays,
            'total_possible_attendances' => $totalPossibleAttendances,
        ],
        'overtime' => [
            'total_hours' => round($totalOvertimeHours, 1),
            'cost' => $overtimeCost,
            'proportion' => $overtimeProportion,
            'avg_daily' => $avgDailyOvertime,
        ],
        'punctuality' => [
            'total_retards' => $totalRetards,
            'total_early_leaves' => $totalEarlyLeaves,
            'avg_per_day' => $totalWorkdays > 0 ? round($totalRetards / $totalWorkdays, 1) : 0,
            'retards_by_day_of_week' => array_values(array_map(function($i) use ($retardsByDayOfWeek, $dayLabels) {
                $dayIdx = ($i - 1) % 7;
                return ['day' => $dayLabels[$dayIdx], 'count' => $retardsByDayOfWeek[$i]];
            }, range(1, 7))),
        ],
        'anomalies' => [
            'total_anomalies' => $totalAnomalies,
            'anomaly_rate' => $anomalyRate,
        ],
        'seasonality' => $seasonalityData,
        'legal_compliance' => $legalViolations,
        'burnout_risk' => [
            'at_risk_count' => count($atRiskUsers),
            'at_risk_users' => $atRiskUsers,
        ],
    ],
    'daily_stats' => $dailyChartData,
    'user_stats' => $userStats,
    'department_stats' => $departmentChartData,
    'recommendations' => $recommendations,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
