<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notificación del sistema</title>
  <style>
    body {
      font-family: 'Inter', Arial, sans-serif;
      background-color: #f9fafb;
      color: #111827;
      line-height: 1.6;
      padding: 20px;
      margin: 0;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      padding: 32px;
    }
    .header {
      text-align: center;
      margin-bottom: 28px;
      border-bottom: 2px solid #f3f4f6;
      padding-bottom: 20px;
    }
    .header h1 {
      font-size: 22px;
      font-weight: 700;
      color: #1f2937;
      margin: 0;
    }
    .content h2 {
      color: #111827;
      font-size: 20px;
      margin-bottom: 16px;
    }
    .content p {
      margin: 12px 0;
      font-size: 15px;
      color: #374151;
    }
    .button {
      display: inline-block;
      margin: 20px 0;
      padding: 14px 28px;
      background-color: #2563eb;
      color: #ffffff !important;
      font-weight: 600;
      text-decoration: none;
      border-radius: 8px;
      font-size: 15px;
      transition: background-color 0.3s ease;
    }
    .button:hover {
      background-color: #1d4ed8;
    }
    .info-box {
      background-color: #eff6ff;
      border: 1px solid #93c5fd;
      border-radius: 8px;
      padding: 16px;
      font-size: 14px;
      color: #1e40af;
      margin: 24px 0;
    }
    .url-fallback {
      background-color: #f3f4f6;
      padding: 12px;
      font-family: monospace;
      border-radius: 6px;
      font-size: 14px;
      word-break: break-all;
    }
    .footer {
      font-size: 13px;
      text-align: center;
      color: #6b7280;
      border-top: 1px solid #e5e7eb;
      margin-top: 28px;
      padding-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- HEADER -->
    <div class="header">
      <h1>Sistema de Notificaciones</h1>
    </div>
    
    <!-- CONTENT -->
    <div class="content">
      <h2>Restabler Contraseña</h2>
      <p>Hola{{ $userName ? ', ' . $userName : '' }},</p>
      <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Para continuar haz clic en el
        siguiente botón, si no fuiste tú, ignora este mensaje.</p>
      
      <div style="text-align: center;">
        <a href="{{ $actionUrl }}" class="button">Recuperar Contraseña</a>
      </div>
      
      <div class="info-box">
        ⚠️ Este enlace tiene validez limitada.  
        Por favor completa el proceso antes de que expire.
      </div>
      
      <p><strong>Si el botón no funciona</strong>, copia y pega esta URL en tu navegador:</p>
      <div class="url-fallback">{{ $actionUrl }}</div>
    </div>
    
    <!-- FOOTER -->
    <div class="footer">
      <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
      <p>&copy; {{ date('Y') }} Todos los derechos reservados.</p>
    </div>
  </div>
</body>
</html>