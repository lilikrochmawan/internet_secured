<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$request = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

// Login user
$user = \App\Models\User::where('level', 'admin')->first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

// Create fake data
$mikrotik_devices = \Illuminate\Support\Facades\DB::table('tbl_mikrotik')->get();
$selected_device_id = 1;
$clientsList = [
    [
        'username' => 'test_user',
        'ip_address' => '10.0.0.2',
        'last_logout' => '-',
        'status' => 'aktif',
        'active_id' => '*1'
    ],
    [
        'username' => 'test_user2',
        'ip_address' => '10.0.0.3',
        'last_logout' => '-',
        'status' => 'terisolir',
        'active_id' => null
    ]
];
$totalActive = 1;
$totalAll = 2;

\Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

try {
    echo "Rendering with connected = true:\n";
    $rendered = view('admin.monitoring.active', [
        'mikrotik_devices' => $mikrotik_devices,
        'selected_device_id' => $selected_device_id,
        'clientsList' => $clientsList,
        'totalActive' => $totalActive,
        'totalAll' => $totalAll,
        'connected' => true
    ])->render();
    echo "Render success! Length: " . strlen($rendered) . "\n";
    echo "Position of content-container: " . strpos($rendered, 'content-container') . "\n";
    
    echo "\nRendering with connected = false:\n";
    $rendered2 = view('admin.monitoring.active', [
        'mikrotik_devices' => $mikrotik_devices,
        'selected_device_id' => $selected_device_id,
        'connected' => false,
        'error' => 'Koneksi router Mikrotik tidak ditemukan.'
    ])->render();
    echo "Render success! Length: " . strlen($rendered2) . "\n";
} catch (\Throwable $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
