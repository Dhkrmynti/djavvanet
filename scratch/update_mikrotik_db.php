<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MikrotikSetting;

$setting = MikrotikSetting::find(1);
$setting->username = 'ap';
$setting->password = 'root_admin.,*';
$setting->is_active = true;
$setting->last_connected_at = now();
$setting->save();

echo "Updated!\n";
echo "Port: " . $setting->port . "\n";
echo "Username: " . $setting->username . "\n";
echo "Active: " . ($setting->is_active ? 'YES' : 'NO') . "\n";
