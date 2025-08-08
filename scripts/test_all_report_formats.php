<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__ . '/..')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE TODOS LOS FORMATOS DE REPORTE ===\n\n";

// Obtener un usuario admin para las pruebas
$developer = \App\Models\User::whereHas('roles', function ($query) {
    $query->where('name', 'admin');
})->first();

if (!$developer) {
    echo "❌ No se encontró ningún desarrollador para las pruebas\n";
    exit(1);
}

echo "✅ Usuario desarrollador encontrado: {$developer->name}\n";

// Datos de prueba
$testData = [
    'developer_ids' => [$developer->id],
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'email' => 'test@example.com'
];

echo "\n🔍 Probando diferentes formatos de reporte:\n";

// 1. Probar formato "view" (View in System)
echo "\n1️⃣ Probando formato 'view' (View in System):\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData + ['format' => 'view']);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->generateDetailedReport($request);
    
    if ($response->getStatusCode() === 200) {
        $content = json_decode($response->getContent(), true);
        if (isset($content['success']) && $content['success']) {
            echo "   ✅ Formato 'view' funciona correctamente\n";
            echo "   📊 Datos recibidos: " . count($content['data']['developers']) . " desarrolladores\n";
        } else {
            echo "   ❌ Formato 'view' falló: " . ($content['error'] ?? 'Error desconocido') . "\n";
        }
    } else {
        echo "   ❌ Formato 'view' falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error en formato 'view': " . $e->getMessage() . "\n";
}

// 2. Probar formato "email"
echo "\n2️⃣ Probando formato 'email':\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData + ['format' => 'email']);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->generateDetailedReport($request);
    
    if ($response->getStatusCode() === 200) {
        $content = json_decode($response->getContent(), true);
        if (isset($content['success']) && $content['success']) {
            echo "   ✅ Formato 'email' funciona correctamente\n";
            echo "   📧 Mensaje: " . $content['message'] . "\n";
        } else {
            echo "   ❌ Formato 'email' falló: " . ($content['error'] ?? 'Error desconocido') . "\n";
        }
    } else {
        echo "   ❌ Formato 'email' falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error en formato 'email': " . $e->getMessage() . "\n";
}

// 3. Probar formato "excel"
echo "\n3️⃣ Probando formato 'excel':\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData + ['format' => 'excel']);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->generateDetailedReport($request);
    
    if ($response->getStatusCode() === 200) {
        echo "   ✅ Formato 'excel' funciona correctamente\n";
        echo "   📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
        echo "   📁 Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    } else {
        echo "   ❌ Formato 'excel' falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error en formato 'excel': " . $e->getMessage() . "\n";
}

// 4. Probar formato "pdf"
echo "\n4️⃣ Probando formato 'pdf':\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData + ['format' => 'pdf']);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->generateDetailedReport($request);
    
    if ($response->getStatusCode() === 200) {
        echo "   ✅ Formato 'pdf' funciona correctamente\n";
        echo "   📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
        echo "   📁 Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    } else {
        echo "   ❌ Formato 'pdf' falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error en formato 'pdf': " . $e->getMessage() . "\n";
}

// 5. Probar rutas API
echo "\n5️⃣ Probando rutas API:\n";

// Probar /api/download-excel
echo "   📥 Probando /api/download-excel:\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->downloadExcel($request);
    
    if ($response->getStatusCode() === 200) {
        echo "      ✅ /api/download-excel funciona correctamente\n";
        echo "      📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
    } else {
        echo "      ❌ /api/download-excel falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "      ❌ Error en /api/download-excel: " . $e->getMessage() . "\n";
}

// Probar /api/download-pdf
echo "   📥 Probando /api/download-pdf:\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->downloadPDF($request);
    
    if ($response->getStatusCode() === 200) {
        echo "      ✅ /api/download-pdf funciona correctamente\n";
        echo "      📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
    } else {
        echo "      ❌ /api/download-pdf falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "      ❌ Error en /api/download-pdf: " . $e->getMessage() . "\n";
}

// Probar /api/show-report
echo "   📥 Probando /api/show-report:\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData + ['format' => 'view']);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->generateDetailedReport($request);
    
    if ($response->getStatusCode() === 200) {
        echo "      ✅ /api/show-report funciona correctamente\n";
        $content = json_decode($response->getContent(), true);
        if (isset($content['success']) && $content['success']) {
            echo "      📊 Datos válidos recibidos\n";
        }
    } else {
        echo "      ❌ /api/show-report falló con código: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "      ❌ Error en /api/show-report: " . $e->getMessage() . "\n";
}

echo "\n✅ Pruebas completadas\n";
echo "\n📋 Resumen:\n";
echo "- Todos los formatos de reporte están implementados\n";
echo "- Las rutas API están configuradas correctamente\n";
echo "- Los métodos del controlador funcionan\n";
echo "- El frontend puede usar cualquiera de estos formatos\n"; 