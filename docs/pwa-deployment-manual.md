# Manual de Procedimientos: Despliegue y Mantenimiento PWA Móvil

Este manual resume la configuración realizada para exponer de forma segura la PWA de repartidores y su API en el puerto **3000 con SSL** utilizando Laragon, Nginx, DuckDNS y Win-ACME en Windows Server.

---

## 1. Arquitectura del Sistema

```
                          [ Internet ]
                               │
                (Puerto 3000 HTTPS Encriptado)
                               │
                               ▼
                        [ Router Local ]
                 (Port Forwarding: 3000 ➔ 3000)
                               │
                               ▼
                     [ Windows Server IP ]
                               │
                      [ Nginx (Laragon) ]
                     (Puerto 3000 con SSL)
                ┌──────────────┴──────────────┐
                ▼                             ▼
        /pwa (PWA Shell)              /api (Laravel API)
    (Servido desde disco)         (Redirigido a php_upstream)
```

- **Dominio Público**: `amicci-repartidores.duckdns.org`
- **Puerto de Exposición**: `3000` (HTTPS)
- **URL de la PWA**: `https://amicci-repartidores.duckdns.org:3000/pwa`
- **URL de la API**: `https://amicci-repartidores.duckdns.org:3000/api`

---

## 2. Software y Servicios Configurados

### A. Laragon (Nginx + PHP-FPM)
Laragon corre el servidor Nginx (puerto 80 local interno y puerto 3000 SSL externo).
- **Ruta de Configuración Nginx PWA**: `C:\laragon\etc\nginx\sites-enabled\amicci-pwa.conf` (o `amicci-api.test.conf`).
- **PHP-FPM**: Vinculado de forma dinámica mediante `fastcgi_pass php_upstream;`.

### B. DuckDNS (DNS Dinámico)
- **Función**: Resuelve el nombre `amicci-repartidores.duckdns.org` a la IP pública de tu servidor.
- **Acceso**: Loguearse en `duckdns.org` con tu cuenta de Google para verificar o forzar la actualización de la IP pública si esta cambia.

### C. Win-ACME (Gestión de Certificados SSL)
- **Función**: Cliente Let's Encrypt para Windows. Solicita el certificado oficial de confianza y lo renueva automáticamente.
- **Ruta de Instalación**: `C:\win-acme\` (o tu carpeta de descargas donde se extrajo).
- **Ubicación de Certificados Exportados**: `C:\laragon\etc\ssl\`
  - `amicci-repartidores.duckdns.org-chain.pem` (Certificado)
  - `amicci-repartidores.duckdns.org-key.pem` (Clave privada)

### D. Script de Automatización DNS (`duckdns.ps1`)
- **Ruta**: `C:\win-acme\duckdns.ps1`
- **Función**: Script en PowerShell que utiliza el Token de DuckDNS para crear/borrar el registro TXT necesario para la validación de Let's Encrypt sin requerir el puerto 80.
- **Contenido**:
  ```powershell
  param([string]$RecordName, [string]$Token)
  $Domain = "amicci-repartidores"
  $DuckToken = "TU_TOKEN_AQUI"
  if ($Token -eq "delete") {
      $Url = "https://www.duckdns.org/update?domains=$Domain&token=$DuckToken&clear=true"
  } else {
      $Url = "https://www.duckdns.org/update?domains=$Domain&token=$DuckToken&txt=$Token"
  }
  Invoke-RestMethod -Uri $Url
  ```

---

## 3. Automatización de Renovación del SSL
Win-ACME ha configurado una **Tarea Programada de Windows** llamada `win-acme renew (acme-v02.api.letsencrypt.org)` que se ejecuta todos los días a las 09:00 AM con un retraso aleatorio.
1. La tarea comprueba si el certificado va a expirar (Let's Encrypt expira cada 90 días).
2. Si le quedan menos de 30 días, ejecuta el script `duckdns.ps1` para actualizar el TXT en DuckDNS.
3. Valida ante Let's Encrypt, descarga los nuevos archivos PEM y los sobreescribe en `C:\laragon\etc\ssl\`.
4. **IMPORTANTE**: Tras la renovación, se debe recargar Nginx para que tome los nuevos certificados (puedes agregar un script de despliegue en Win-ACME para automatizar esto si lo deseas).

---

## 4. Guía de Resolución de Problemas (Troubleshooting)

### Problema A: El sitio da "502 Bad Gateway" en el móvil
- **Causa**: Nginx está corriendo, pero no puede comunicarse con PHP-FPM.
- **Solución**: 
  1. Abre Laragon y asegúrate de que **PHP** esté iniciado.
  2. Verifica el puerto de PHP-FPM en Laragon (clic derecho ➔ PHP ➔ Quick Settings ➔ PHP-FPM Port).
  3. Asegúrate de que el archivo `.conf` en `sites-enabled` tenga `fastcgi_pass php_upstream;`.

### Problema B: El sitio da "No se puede acceder a este sitio" (Timeout)
- **Causa**: El puerto 3000 está bloqueado o redirigido a otra máquina.
- **Solución**:
  1. Verifica que Nginx esté corriendo en el servidor.
  2. Comprueba en el **Firewall de Windows Defender** que la regla de entrada para el puerto `3000 TCP` esté habilitada.
  3. Comprueba el **Port Forwarding de tu Router**: El puerto público 3000 debe redirigir al puerto privado 3000 de la IP interna del servidor.

### Problema C: Nginx no inicia o da error al recargar
- **Causa**: Procesos colgados de Nginx en segundo plano o error de sintaxis.
- **Solución**:
  1. En una consola como Administrador en el servidor, ejecuta:
     ```cmd
     taskkill /f /im nginx.exe
     ```
  2. Inicia Nginx desde Laragon.
  3. Si sigue sin iniciar, abre `C:\laragon\bin\nginx\nginx-1.22.0\logs\error.log` para ver la causa exacta (por ejemplo, rutas de certificado mal escritas).

### Problema D: El HTTPS vuelve a salir tachado ("No seguro")
- **Causa**: El certificado SSL expiró y la tarea de renovación falló.
- **Solución**:
  1. Abre PowerShell como Administrador en `C:\win-acme\`.
  2. Ejecuta `./wacs.exe --renew` para forzar la renovación manual y ver qué error de red o script está ocurriendo.
