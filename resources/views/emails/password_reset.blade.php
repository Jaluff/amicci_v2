<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecimiento de Contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px;">
    <h2>Solicitud de Restablecimiento de Contraseña</h2>
    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el Área de Clientes de Amicci.</p>
    <p>Haz clic en el siguiente enlace para establecer una nueva contraseña:</p>
    <p style="margin: 20px 0;">
        <a href="{{ $url }}" style="background-color: #ea580c; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
            Restablecer Contraseña
        </a>
    </p>
    <p>Si no realizaste esta solicitud, puedes ignorar este mensaje.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 12px; color: #777;">Transporte Amicci &copy; {{ date('Y') }}</p>
</body>
</html>
