# Asesoría Bot UP

Plataforma web (PHP 8 + MySQL) y bot de Telegram para la gestión de asesorías académicas (UPSLP).

## Estructura del proyecto
- `/config`: Configuración de base de datos y token del bot.
- `/modelos`, `/vistas`, `/controladores`: Plataforma web en PHP.
- `/bot`: Scripts en PHP para la API de Telegram (long polling).
- `/sql`: Script de creación de base de datos y datos de prueba.
- `/docs`: Entregables documentales del proyecto.

## Instalación y configuración local
1. Clonar este repositorio en el servidor local (XAMPP/Laragon).
2. Importar el script `/sql/schema.sql` en MySQL/MariaDB.
3. Copiar `config/config.example.php` a `config/config.php` y colocar las credenciales locales de MySQL y el token generado con `@BotFather`.
4. Para encender el bot en consola:
   ```bash
   php bot/poll.php