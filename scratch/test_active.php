<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$request = Illuminate\Http\Request::create('/administrator/monitoring/active', 'GET');
$app->instance('request', $request);

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

// Login user
$user = \App\Models\User::where('level', 'admin')->first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

\Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

try {
    $controller = $app->make(\App\Http\Controllers\Admin\AdminMonitoringController::class);
    $request = new \Illuminate\Http\Request();
    
    $response = $controller->activeClients($request);
    if ($response instanceof \Illuminate\View\View) {
        $rendered = $response->render();
        $pos = strpos($rendered, 'class="content-container"');
        if ($pos !== false) {
            echo "Content Container found at position $pos:\n";
            echo substr($rendered, $pos, 3000) . "\n";
        } else {
            echo "content-container class not found in rendered HTML!\n";
        }
    } else {
        echo "Response is of class: " . get_class($response) . "\n";
    }
} catch (\Throwable $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo $e->getFile() . " on line " . $e->getLine() . "\n";
}
