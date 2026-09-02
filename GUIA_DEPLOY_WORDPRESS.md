# Guia de despliegue en produccion con WordPress

Esta guia explica como publicar este sistema PHP usando una extension de WordPress que permite crear, subir y modificar archivos, junto con acceso a TiDB Cloud. No se asume acceso directo a cPanel, FTP, SSH ni configuracion del servidor.

## Importante: limitacion tecnica

Con el plugin de gestion de archivos **si se puede subir el proyecto manualmente**. La biblioteca de medios de WordPress no sirve para esto, pero una extension con acceso al sistema de archivos puede crear la carpeta, subir PHP, CSS, imagenes, `.env` y `.htaccess`.

La extension no puede activar extensiones PHP ni cambiar la configuracion global del servidor. Como se asumira que no se puede solicitar ninguna intervencion, la aplicacion debe usar **TiDB Cloud Data Service por HTTPS** y no una conexion MySQL directa. La base de datos se administra desde TiDB Cloud.

Para que el sistema funcione manteniendo la seguridad, hace falta una de estas opciones:

1. **Configurar TiDB Cloud Data Service** con endpoints autenticados y consultas fijas.
2. **Adaptar la capa de acceso del proyecto** para consumir esos endpoints mediante HTTPS.
3. **Subir la aplicacion adaptada con la extension de archivos**, siguiendo el procedimiento de esta guia.

Un plugin de administrador de archivos no convierte automaticamente esta aplicacion en un plugin de WordPress. Ademas, instalar uno de origen desconocido puede exponer todo el sitio.

### Lo que no funcionara

- Subir los archivos desde **Medios > Añadir nuevo** y abrir el PHP desde una URL.
- Crear una pagina de WordPress con el contenido de `index.php`.
- Pegar todo el proyecto en un bloque de HTML.
- Instalar solamente extensiones de WordPress esperando que habiliten `pdo_mysql`, `gd` o `fileinfo`.
- Conectar directamente a TiDB Cloud desde JavaScript del navegador; expondria las credenciales.

### Recomendacion para este proyecto

Mantener el proyecto como aplicacion PHP independiente y subirlo con la extension de archivos. Asi se conservan las pantallas, acciones POST, autenticacion, CSRF, captcha, permisos por rol y generacion de la constancia, mientras la capa de datos consulta TiDB Cloud mediante HTTPS.

## 1. Como quedara instalado

La opcion recomendada es colocar el proyecto en una carpeta independiente del sitio WordPress:

```text
public_html/
  .htaccess                 # pertenece normalmente a WordPress
  wp-admin/
  wp-content/
  wp-includes/
  titulacion/                # si la extension permite acceder a public_html
    public/                 # archivos que abre el navegador
      index.php
      dashboard.php
      admin_login.php
      admin_dashboard.php
      assets/
    src/                    # logica PHP, no debe ser publica
    db/                     # respaldos SQL, no debe ser publica
    .env                    # credenciales, no debe ser publica
    .htaccess               # proteccion del proyecto
```

  El archivo `.htaccess` que viene en la raiz del proyecto ya esta preparado para este escenario: bloquea `src/`, `db/`, `.env`, archivos SQL, registros y certificados PEM. Subelo desde la extension exactamente junto a `public/` y `src/`.

Si la extension solo permite trabajar dentro de `wp-content`, usa esta alternativa:

```text
public_html/wp-content/titulacion/
  public/
  src/
  db/
  .env
  .htaccess
```

En ese caso la URL sera `https://tudominio.com/wp-content/titulacion/public/index.php`. La primera ubicacion es preferible; si solo existe la segunda, funciona siempre que el servidor ejecute PHP en esa carpeta y acepte `.htaccess`.

La aplicacion quedaria disponible, por ejemplo, en:

```text
https://tudominio.com/titulacion/public/index.php
```

No se debe copiar el contenido de `public/` directamente dentro de `wp-content` ni dentro de una pagina de WordPress. Este proyecto es una aplicacion PHP independiente; una pagina de WordPress no ejecuta archivos PHP subidos como contenido.

## 2. Datos que debes obtener desde TiDB Cloud

Antes de adaptar y subir el proyecto, obtén desde TiDB Cloud:

1. URL base de Data Service.
2. Metodo de autenticacion y token de produccion.
3. Identificadores de los endpoints para cada operacion.
4. Base de datos y tablas que usaran los endpoints.
5. Acceso al SQL Editor para importar `db/titulacion.sql` y aplicar el campo `administradores.rol`.

### Si el administrador pregunta por `.htaccess`

La aplicacion actual no necesita `mod_rewrite` para sus rutas. Si el servidor es Apache o LiteSpeed, debe permitir `.htaccess` para bloquear `src/`, `db/` y `.env`. Si es Nginx, el administrador debe configurar esos bloqueos directamente en el servidor.

### Requisito PHP minimo

Con los cambios actuales ya no se necesitan `gd`, `fileinfo`, `mbstring`, Composer ni `pdo_mysql`. La aplicacion necesita PHP con sesiones y capacidad de realizar peticiones HTTPS salientes, requisitos normalmente presentes para WordPress.

TiDB Cloud Data Service debe entregar al proyecto la URL base y el metodo de autenticacion. Esos datos no se pueden inventar ni obtener automaticamente desde el repositorio.

### Arquitectura segura sin `pdo_mysql`

No intentes sustituir `pdo_mysql` con una conexion desde el navegador ni publiques las credenciales de TiDB Cloud. La alternativa es:

1. En TiDB Cloud crea **Data Service** o el servicio HTTP equivalente disponible para tu cluster.
2. Crea endpoints separados para las operaciones que necesita la aplicacion: iniciar sesion, registrar alumno, consultar perfil, actualizar datos, listar alumnos, cambiar estatus y exportar.
3. No expongas un endpoint que acepte SQL libre. Cada endpoint debe ejecutar una consulta fija y validar los parametros recibidos.
4. Usa un usuario de base de datos con permisos minimos: solo `SELECT`, `INSERT` y `UPDATE` donde sean necesarios. Reserva `DELETE` para la operacion administrativa que realmente lo requiera.
5. Protege los endpoints con autenticacion y HTTPS. Usa un token distinto para produccion y con el menor alcance posible.
6. Guarda el token unicamente en `.env`, fuera de `public/`. Nunca lo pongas en JavaScript, HTML, la URL, cookies del alumno ni respuestas JSON.
7. Cambia la capa de acceso de `getConexion()` para que PHP llame a esos endpoints mediante una peticion HTTPS del servidor. No se deben modificar todas las pantallas para que hablen directamente con TiDB.
8. Mantén en el servidor la validacion de sesiones, CSRF, roles, contraseñas, rate limiting y permisos. La API no reemplaza esos controles.
9. Configura limites de solicitudes, registros de auditoria y una lista de origenes o IP permitidas cuando Data Service lo permita.
10. Prueba primero en un entorno separado y migra todas las consultas antes de retirar la conexion MySQL. No mezcles algunas consultas por MySQL y otras por API sin probar transacciones y errores.

Esta alternativa requiere adaptar el codigo con el contrato real de Data Service antes de subirlo. No recomendamos usar una API casera instalada como archivo publico ni poner las credenciales de TiDB Cloud en el navegador. Si Data Service no esta disponible, no existe una forma segura de conectar este proyecto con TiDB Cloud usando solamente la extension de archivos de WordPress.

### Comprobacion desde WordPress

Puedes crear temporalmente `public/php-check.php` con:

```php
<?php
foreach (['curl', 'openssl', 'session'] as $extension) {
    echo $extension . ': ' . (extension_loaded($extension) ? 'OK' : 'FALTA') . PHP_EOL;
}
```

Debe abrirlo desde la URL de la aplicacion y eliminarlo inmediatamente despues:

```text
https://tudominio.com/titulacion/public/php-check.php
```

El resultado esperado es `OK` para `openssl` y `session`. `curl` es opcional porque la alternativa puede usar el cliente HTTPS nativo de PHP. Elimina `php-check.php` inmediatamente despues de comprobarlo.

## 3. Preparar los archivos desde WordPress

1. Haz una copia de seguridad del sitio WordPress usando el plugin de respaldo aprobado por el administrador.
2. No subas `doc viejos/`.
3. No subas `.git/`, `.vscode/`, archivos temporales ni copias de SQL innecesarias.
4. No necesitas ejecutar Composer ni subir `vendor/` para este proyecto.
5. Comprueba que `public/assets/img/` incluya `logo.png` y `logotipo.png`.
6. No subas `.env` a GitHub. Se creara directamente con la extension de archivos en la carpeta raiz del proyecto.

## 4. Configurar la base de datos en TiDB Cloud

Estas tareas se realizan en TiDB Cloud, no dentro de WordPress:

1. Abre el cluster de TiDB Cloud y entra al **SQL Editor**.
2. Crea o selecciona la base de datos de produccion.
3. Importa o ejecuta el contenido de `db/titulacion.sql`.
4. Verifica que la tabla `administradores` tenga la columna `rol`:

   ```sql
   SHOW COLUMNS FROM administradores LIKE 'rol';
   ```

  Si la base de datos ya existia y no tiene esa columna, ejecutar una sola vez:

   ```sql
   ALTER TABLE administradores
     ADD COLUMN rol TINYINT(1) NOT NULL DEFAULT 0;
   ```

5. Marca como superadministrador la cuenta que debe gestionar admins:

   ```sql
   UPDATE administradores SET rol = 1 WHERE usuario = 'USUARIO_SUPERADMIN';
   ```

   Sustituye `USUARIO_SUPERADMIN` por el usuario real.

No guardes contraseñas en texto plano. La aplicacion espera contraseñas generadas con `password_hash`.

Si el SQL Editor no permite importar archivos completos, pega y ejecuta el contenido por bloques o solicita que habiliten la importacion. No ejecutes el archivo sobre la base de datos de WordPress.

## 5. Subir las carpetas desde WordPress

Desde WordPress, abre la extension de gestion de archivos autorizada. El cargador de medios de WordPress no sirve para esta tarea porque bloquea o modifica archivos PHP.

Si la extension permite acceder a la raiz publica, crea una carpeta independiente llamada `titulacion` fuera de `wp-content` y sube estas carpetas y archivos:

```text
public/
src/
db/                 # puede omitirse despues de importar la BD
.env
.htaccess
```

El resultado importante es:

```text
public_html/titulacion/public/index.php
public_html/titulacion/src/config/config.php
public_html/titulacion/src/alumnos/generar_constancia.php
public_html/titulacion/.env
```

No renombres `public` ni `src`: las rutas internas del proyecto dependen de esos nombres. Si el plugin solo permite subir dentro de `wp-content`, usa la ubicacion alternativa indicada abajo y confirma primero que el servidor ejecutara PHP en esa carpeta.

Si solo permite trabajar dentro de `wp-content`, crea `wp-content/titulacion/` y coloca ahi exactamente las mismas carpetas. Despues utiliza la URL alternativa indicada en la seccion 10.

Tambien debes subir los archivos `.htaccess` que estan dentro de `src/` y `db/`. Funcionan como una segunda barrera si alguien intenta abrir directamente una de esas carpetas.

## 6. Configurar TiDB Cloud y el certificado CA

Si la base de datos de produccion es **TiDB Cloud**, no basta con usuario, contraseña, host y puerto. La conexion debe usar TLS y necesita el certificado CA que descarga TiDB Cloud.

### Descargar el certificado desde TiDB Cloud

La descarga se realiza desde TiDB Cloud y la subida del archivo se realiza con la extension de gestion de archivos de WordPress. El panel de WordPress no puede activar TLS, pero si puede colocar el certificado en la carpeta del proyecto.

1. Entra a TiDB Cloud y abre el cluster correspondiente.
2. Abre **Connect**.
3. Selecciona **General** o **Public Endpoint**, segun lo que muestre tu panel.
4. Selecciona el metodo de conexion **PHP/PDO** o **MySQL**.
5. Descarga el certificado **CA**. TiDB puede entregarlo con un nombre como `ca.pem`.
6. No descargues ni subas una llave privada: para esta aplicacion solo se necesita el certificado CA publico.
7. Con la extension de WordPress, sube el archivo CA a una carpeta no publica. Para usar la ruta predeterminada del proyecto, renombralo como `isrgrootx1.pem` y colocalo en:

  ```text
  public_html/titulacion/src/config/isrgrootx1.pem
  ```

  En este proyecto `src/config/` no debe ser accesible desde el navegador porque contiene logica interna.

El archivo CA debe conservar el formato PEM y comenzar normalmente con:

```text
-----BEGIN CERTIFICATE-----
```

No pegues el contenido del certificado directamente en el archivo `.env`. Guarda el certificado como archivo. Si lo nombras `isrgrootx1.pem` y lo colocas en `src/config/`, no necesitas agregar `DB_SSL_CA` porque `config.php` ya usa esa ruta predeterminada.

## 7. Crear el archivo `.env` desde WordPress

Con la extension de gestion de archivos, crea en la raiz del proyecto el archivo `.env`:

```dotenv
DB_ENVIRONMENT="REMOTE"
DB_HOST_REMOTE="HOST_DE_LA_BASE_DE_DATOS"
DB_NAME_REMOTE="NOMBRE_DE_LA_BASE_DE_DATOS"
DB_USER_REMOTE="USUARIO_DE_LA_BASE_DE_DATOS"
DB_PASS_REMOTE="CONTRASENA_DE_LA_BASE_DE_DATOS"
DB_PORT_REMOTE="4000"
DB_CHARSET="utf8mb4"
```

Usa exactamente los datos entregados por TiDB Cloud. En el caso de TiDB Cloud, normalmente el puerto es `4000`, no `3306`, y el usuario puede incluir un sufijo proporcionado por TiDB.

Como normalmente no se conoce la ruta absoluta del servidor, usa el valor predeterminado del proyecto: el archivo debe llamarse exactamente `isrgrootx1.pem` y estar en:

```text
public_html/titulacion/src/config/isrgrootx1.pem
```

No es necesario agregar `DB_SSL_CA` si el archivo se llama `isrgrootx1.pem` y esta en esa carpeta.

En TiDB Cloud revisa tambien que el endpoint publico este habilitado y que la direccion IP del hosting este permitida en **Trusted IP Addresses** o la lista equivalente del cluster.

Protege el archivo usando las opciones de permisos de la extension si las ofrece. Nunca lo coloques dentro de `public/`.

No lo subas a GitHub, no lo coloques dentro de `public/` y no lo compartas en capturas de pantalla.

## 8. Dependencias externas

El proyecto ya no requiere Composer, `vendor/`, `vlucas/phpdotenv` ni `gregwar/captcha`. El captcha se genera con SVG y el archivo `.env` se lee con funciones nativas de PHP.

## 9. Proteccion del proyecto

Con la extension de WordPress, crea o sube `.htaccess` dentro de la carpeta raiz del proyecto. Apache o LiteSpeed lo aplicara automaticamente solo si el servidor permite `AllowOverride`; la extension no puede activar esa opcion. WordPress no lo activa desde su panel.

Si el servidor es Nginx, `.htaccess` se ignora. En ese caso solicita al proveedor que bloquee `src/`, `db/` y `.env` desde la configuracion del dominio, o usa un document root apuntando directamente a `titulacion/public`.

El proyecto ya incluye `titulacion/.htaccess`. Si la extension no muestra archivos que comienzan con punto, activa la opcion **Mostrar archivos ocultos**. Si permite crear archivos, crea `.htaccess` manualmente y pega este contenido. Su objetivo es impedir que el navegador descargue `.env`, SQL, codigo fuente, dependencias y archivos de configuracion:

```apache
Options -Indexes

<FilesMatch "^(\.env|.*\.sql|.*\.log|.*\.pem)$">
    Require all denied
</FilesMatch>

RewriteEngine On
RewriteRule ^(?:src|db|doc\ viejos)(?:/|$) - [F,L,NC]
```

Si el hosting usa Apache 2.2 y rechaza `Require all denied`, consulta al soporte para habilitar Apache 2.4. Si al abrir una URL protegida aparece error 500, retira temporalmente el archivo y consulta los registros o al soporte. No reemplaces a ciegas el `.htaccess` de WordPress en `public_html`; este archivo va dentro de `titulacion/`.

Tambien puedes proteger carpetas sensibles con un `.htaccess` dentro de cada una (`src/` y `db/`):

```apache
Require all denied
```

El archivo `public/.htaccess` no es obligatorio para esta aplicacion porque cada pantalla tiene su propio archivo PHP. No agregues reglas de WordPress dentro de `public/` sin probarlas.

## 10. Elegir la URL correcta

### Opcion A: usar `/titulacion/public/`

Es la opcion que funciona con la estructura actual y no requiere cambios de codigo:

```text
https://tudominio.com/titulacion/public/index.php
```

Prueba tambien:

```text
https://tudominio.com/titulacion/public/admin_login.php
```

### Opcion B: usar un subdominio o document root apuntando a `public/`

Si el hosting permite elegir el document root, apunta el subdominio a:

```text
/home/USUARIO/public_html/titulacion/public
```

Por ejemplo:

```text
https://tramites.tudominio.com/index.php
```

Esta opcion es mas limpia, porque `src/`, `db/` y `.env` quedan fuera del document root. Si el hosting permite hacerlo, es la configuracion preferida.

## 11. Comprobaciones despues de subir

Realiza estas pruebas en este orden:

1. Abre `public/index.php` y confirma que cargan CSS, fondos y `public/assets/img/logotipo.png`.
2. Registra un alumno de prueba.
3. Inicia sesion como alumno.
4. Completa el perfil y abre el dashboard.
5. Abre **Generar Constancia** y confirma que se muestra el logotipo y que el enlace de imagen no devuelve 404.
6. Usa **Imprimir / Guardar PDF** y verifica que el formato cabe en una hoja carta.
7. Cierra sesion y confirma que no se puede volver al dashboard usando el boton atras.
8. Inicia sesion como administrador.
9. Comprueba que un admin normal no puede abrir `gestion_admins.php`.
10. Comprueba que el superadmin puede crear, cambiar contraseña y dar de baja un admin normal.
11. Prueba filtros y exportacion CSV desde el panel administrativo.
12. Abre estas URL en una ventana privada; todas deben devolver 403, 404 o una denegacion equivalente:

    ```text
    https://tudominio.com/titulacion/.env
    https://tudominio.com/titulacion/db/titulacion.sql
    https://tudominio.com/titulacion/src/config/config.php
    ```

## 12. Errores frecuentes

| Sintoma | Causa probable | Solucion |
|---|---|---|
| CSS, logo o captcha no cargan | La URL se esta abriendo desde una carpeta distinta o falta `public/` | Usa la URL `/titulacion/public/index.php` o configura el document root a `public/` |
| Error de conexion PDO | `.env` incorrecto, puerto bloqueado o usuario sin permisos | Revisa host, puerto, nombre, usuario, contraseña y permisos de BD |
| `SSL connection error` o `unable to get local issuer certificate` | Falta el CA de TiDB Cloud, la ruta es incorrecta o el PEM esta dañado | Sube el CA como `src/config/isrgrootx1.pem` y revisa que comience con `BEGIN CERTIFICATE` |
| `No such file or directory` para el certificado | El CA no esta en `src/config/isrgrootx1.pem` | Verifica la ubicacion y el nombre del archivo desde la extension de WordPress |
| Falta `pdo_mysql` | El PHP del sitio no puede abrir una conexion MySQL directa | Usa la migracion a Data Service mediante HTTPS siguiendo la seccion de contingencia |
| TiDB Cloud rechaza la conexion | El endpoint no esta habilitado o la IP del hosting no esta autorizada | Revisa **Connect**, el endpoint publico, el puerto `4000` y la lista de IP confiables |
| `Unknown column 'rol'` | La BD existente no fue actualizada | Ejecuta el `ALTER TABLE` del paso 4 |
| Error al generar captcha | `src/captcha/captcha.php` no se subio completo o la sesion no funciona | Vuelve a subir `src/captcha/captcha.php` y confirma que las sesiones PHP esten activas |
| La constancia muestra imagen rota | Ruta o archivo incorrecto | Debe existir `public/assets/img/logo.png` y la constancia debe usar `../../public/assets/img/logo.png` cuando se abre por la ruta actual |
| `.htaccess` causa error 500 | Directivas no permitidas por el hosting | Retira temporalmente la regla, pide habilitar `AllowOverride` y revisa el registro de errores |
| WordPress redirige la URL | La URL apunta a una pagina de WordPress, no al PHP | Abre la ruta real de la aplicacion o usa un subdominio con document root en `public/` |

## 13. Checklist final

- [ ] Backup de la base de datos realizado.
- [ ] PHP, `openssl` y sesiones verificados.
- [ ] `public/` y `src/` subidas completas.
- [ ] `.env` creado en la raiz y fuera de `public/`.
- [ ] Certificado CA de TiDB Cloud descargado y subido fuera de `public/`.
- [ ] `DB_SSL_CA` apunta al archivo PEM correcto.
- [ ] Puerto, endpoint y lista de IP confiables de TiDB Cloud comprobados.
- [ ] Data Service configurado y probado mediante HTTPS.
- [ ] Base de datos importada.
- [ ] Columna `administradores.rol` verificada.
- [ ] Usuario superadmin configurado.
- [ ] `.htaccess` de proteccion instalado en la carpeta del proyecto.
- [ ] Logo y captcha comprobados.
- [ ] Login de alumno y administrador comprobados.
- [ ] Generacion de constancia comprobada.
- [ ] Acceso a `.env`, SQL y `src/` bloqueado.
- [ ] No hay credenciales reales en GitHub ni en el codigo.
