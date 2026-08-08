<?php
$json = file_get_contents(__DIR__ . '/data.json');
$data = json_decode($json, true);

echo "records: " . count($data['data']) . "\n";

$types = array_count_values(array_column($data['data'], 'type'));
echo "Types: " . json_encode($types) . "\n";

$dates = array_unique(array_map(function($r) { return substr($r['check_time'], 0, 10); }, $data['data']));
echo "Date range: " . min($dates) . " to " . max($dates) . "\n";
echo "Total dates: " . count($dates) . "\n";

$users = array_unique(array_column($data['data'], 'name'), SORT_STRING);
echo "Unique names: " . count($users) . "\n";
echo "Users: " . implode(', ', $users) . "\n";

$depts = array_count_values(array_column($data['data'], 'department'));
echo "Departments: " . json_encode($depts) . "\n";
