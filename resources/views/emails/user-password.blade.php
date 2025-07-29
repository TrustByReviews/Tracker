<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Credenciales de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
        }
        .credentials {
            background-color: white;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            background-color: #f3f4f6;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 Tracker - Credenciales de Acceso</h1>
    </div>
    
    <div class="content">
        <h2>Hola {{ $userName }},</h2>
        
        <p>Tu cuenta ha sido creada exitosamente en nuestro sistema de gestión de proyectos <strong>Tracker</strong>.</p>
        
        <p>A continuación encontrarás tus credenciales de acceso:</p>
        
        <div class="credentials">
            <div class="field">
                <div class="label">Email:</div>
                <div class="value">{{ $email }}</div>
            </div>
            
            <div class="field">
                <div class="label">Contraseña:</div>
                <div class="value">{{ $password }}</div>
            </div>
        </div>
        
        <div class="warning">
            <strong>⚠️ Importante:</strong>
            <ul>
                <li>Guarda estas credenciales en un lugar seguro</li>
                <li>Te recomendamos cambiar tu contraseña después del primer inicio de sesión</li>
                <li>No compartas estas credenciales con nadie</li>
            </ul>
        </div>
        
        <p>Puedes acceder al sistema en: <strong>http://127.0.0.1:8000</strong></p>
        
        <p>Si tienes alguna pregunta, no dudes en contactar al administrador del sistema.</p>
        
        <p>¡Bienvenido al equipo!</p>
    </div>
    
    <div class="footer">
        <p>Este es un email automático, por favor no respondas a este mensaje.</p>
        <p>&copy; {{ date('Y') }} Tracker. Todos los derechos reservados.</p>
    </div>
</body>
</html> 