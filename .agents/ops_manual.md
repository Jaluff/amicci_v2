# Guía de Operaciones para IA (Ops Manual) - Proyecto Amicci2

Esta guía contiene reglas específicas del entorno para optimizar la velocidad y precisión de la IA en este repositorio. Complementa las "Antigravity Laws" definidas en las reglas de usuario.

## 1. Gestión del Sistema de Archivos (WSL)
- **Base Path**: Todas las búsquedas y accesos a archivos deben realizarse sobre el path de red de WSL: `//wsl.localhost/Ubuntu/home/emilio/proyectos/amicci2-sys/`.
- **Evitar C:\Ubuntu**: El mapeo directo a disco en Windows suele ser incompleto o lento. Usar siempre el path de red mencionado arriba.

## 2. Ejecución de Comandos (Laravel Sail)
- **Contexto**: El proyecto corre sobre Sail en WSL.
- **Preferencia**: Usar siempre `wsl -e ./vendor/bin/sail [comando]` o simplemente `wsl -e php artisan [comando]` si el entorno lo permite directamente.
- **Búsquedas**: Para búsquedas rápidas (`grep`, `find`), usar `wsl -e grep ...` en lugar de herramientas nativas de PowerShell si el mapeo de red es lento.

## 3. Preferencias de UI/UX Recurrentes
- **Centrado en DataTables**: Las columnas de estado (badges), links de documentos apilados y acciones deben estar centradas por defecto usando `className: 'text-center'` en la configuración de la columna JS de DataTables.
- **Estética Premium**: Siempre dar prioridad a la elegancia visual sobre lo funcional básico.

## 4. Comunicación Directa
- Si la ruta de un archivo no es clara tras un intento, preguntar por el namespace o el nombre del componente específico antes de realizar escaneos profundos en todo el disco `C:\`.
