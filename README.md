# Proyecto Titulación

Sistema de gestión de titulación desarrollado en PHP con soporte para conexión a base de datos local (MySQL/XAMPP) o remota (TiDB Cloud).

## Requisitos previos

- PHP 8.0 o superior
- [Composer](https://getcomposer.org/download/) instalado y accesible desde la terminal
- XAMPP (si se desea correr en entorno local) o un servidor PHP con MySQL
- Extensiones de PHP habilitadas: `gd` y `fileinfo` (ver instrucciones abajo)

## 1. Clonar / obtener el proyecto

```bash
git clone <url-del-repositorio>
cd <carpeta-del-proyecto>
```

## 2. Instalar dependencias con Composer

Desde la raíz del proyecto:

```bash
composer install
```

Si no tienes Composer instalado, descárgalo desde https://getcomposer.org/download/ e instálalo siguiendo las instrucciones para tu sistema operativo. Verifica que quedó disponible en la terminal con:

```bash
composer --version
```

## 3. Habilitar las extensiones `gd` y `fileinfo` en `php.ini`

### Si usas XAMPP

1. Abre el panel de control de XAMPP.
2. Haz clic en **Config** (junto a Apache) → **PHP (php.ini)**. Esto abrirá el archivo `php.ini` que XAMPP está usando (normalmente en `C:\xampp\php\php.ini`).
3. Busca las siguientes líneas (usa Ctrl+F):

   ```ini
   ;extension=gd
   ;extension=fileinfo
   ```

4. Quita el punto y coma (`;`) al inicio de cada línea para que quede así:

   ```ini
   extension=gd
   extension=fileinfo
   ```

5. Guarda el archivo.
6. Reinicia Apache desde el panel de XAMPP (botón **Stop** y luego **Start** junto a Apache) para que los cambios surtan efecto.

### Si usas PHP por línea de comandos (sin XAMPP)

1. Ubica tu archivo `php.ini` activo con:

   ```bash
   php --ini
   ```

2. Ábrelo con tu editor de texto y realiza el mismo cambio: descomenta (quita el `;`) de las líneas `extension=gd` y `extension=fileinfo`.
3. Guarda el archivo y verifica que las extensiones quedaron activas:

   ```bash
   php -m | grep -i gd
   php -m | grep -i fileinfo
   ```

   Ambos comandos deben mostrar el nombre de la extensión en la salida.

## 4. Configurar el archivo `.env`

Copia el archivo de ejemplo y ajusta los valores según tu entorno:

```bash
cp .env.example .env
```

El `.env` define si el proyecto usará la base de datos **local** o **remota** mediante la variable `DB_ENVIRONMENT`:

```dotenv
# Cambia a 'LOCAL' o 'REMOTE' según la BD que quieras usar
DB_ENVIRONMENT="LOCAL"

# Configuración Base de Datos Local
DB_HOST_LOCAL="localhost"
DB_NAME_LOCAL="titulacion"
DB_USER_LOCAL="root"
DB_PASS_LOCAL=""
DB_PORT_LOCAL="3306"

# Configuración Base de Datos Remota
DB_HOST_REMOTE="<host-remoto>"
DB_NAME_REMOTE="<nombre-bd-remota>"
DB_USER_REMOTE="<usuario-remoto>"
DB_PASS_REMOTE="<password-remoto>"
DB_PORT_REMOTE="4000"
```

> ⚠️ **Importante:** el archivo `.env` contiene credenciales sensibles (usuario y contraseña de la base de datos remota). No lo subas a repositorios públicos ni lo compartas. Asegúrate de que esté incluido en `.gitignore`. Si estas credenciales ya se compartieron o se filtraron, se recomienda rotarlas (cambiar la contraseña) desde el panel de tu proveedor de base de datos.

## 5. Preparar la base de datos local (si usas `DB_ENVIRONMENT="LOCAL"`)

1. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
2. Abre phpMyAdmin en `http://localhost/phpmyadmin`.
3. Crea una base de datos llamada `titulacion` (o el nombre definido en `DB_NAME_LOCAL`).
4. Importa el archivo `.sql` del proyecto (si se incluye uno) desde la pestaña **Importar**.

## 6. Ejecutar el proyecto

### Opción A: Con el servidor embebido de PHP (línea de comandos)

Desde la raíz del proyecto:

```bash
php -S localhost:8000
```

Luego abre tu navegador en `http://localhost:8000`.

### Opción B: Con XAMPP

1. Copia (o mueve) la carpeta del proyecto dentro de `C:\xampp\htdocs\` (Windows) o `/Applications/XAMPP/htdocs/` (Mac).
2. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
3. Abre tu navegador en `http://localhost/<nombre-de-la-carpeta-del-proyecto>`.

## 7. Verificación rápida

- Confirma que Composer instaló las dependencias: debe existir la carpeta `vendor/`.
- Confirma que `gd` y `fileinfo` están activas con `php -m`.
- Verifica que la conexión a la base de datos funciona cambiando `DB_ENVIRONMENT` entre `"LOCAL"` y `"REMOTE"` y probando el acceso a la aplicación en cada caso.

## Solución de problemas comunes

| Problema | Posible causa | Solución |
|---|---|---|
| `Class "GdImage" not found` o errores de imágenes | Extensión `gd` no habilitada | Revisar el paso 3 y reiniciar Apache/terminal |
| `finfo_open(): Failed to load` o errores al subir archivos | Extensión `fileinfo` no habilitada | Revisar el paso 3 y reiniciar Apache/terminal |
| `composer: command not found` | Composer no instalado o no está en el PATH | Reinstalar Composer y agregarlo al PATH del sistema |
| No conecta a la base de datos remota | Firewall, credenciales incorrectas o IP no permitida en TiDB Cloud | Verificar las credenciales en `.env` y que tu IP esté en la whitelist del proveedor |