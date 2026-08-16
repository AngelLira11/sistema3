# Sistema de Titulación

## Cómo eliges local o producción

La app decide la base de datos con esta variable:

```bash
APP_ENV=local
```

o

```bash
APP_ENV=production
```

- `APP_ENV=local` = usa [.env.local](.env.local) y la base local
- `APP_ENV=production` = usa [.env](.env) y la base remota

## 1) Local

Esto significa que la app corre en tu computadora y usa la base de datos local.

1. Crea una base llamada `titulacion` en MySQL.
2. Deja [.env.local](.env.local) así:

```env
APP_ENV=local
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=titulacion
DB_PORT=3306
DB_CHARSET=utf8mb4
```

3. Ejecuta:

```bash
set APP_ENV=local
php -S localhost:8000 -t public
```

4. Abre:

```text
http://localhost:8000
```

## 2) Producción (pero en tu localhost)

Aquí no significa que cambie la URL. Significa que la app sigue corriendo en tu máquina, pero conectándose a la base remota de producción.

1. En [.env](.env) pon la base remota real:

```env
APP_ENV=production
DB_HOST=gateway01.us-east-1.prod.aws.tidbcloud.com
DB_USER=3uSsvXzWqJayFsh.root
DB_PASS=mSxyPOXt5uE2dswB
DB_NAME=titulacion_db
DB_PORT=4000
DB_CHARSET=utf8mb4
```

2. Ejecuta:

```bash
set APP_ENV=production
php -S localhost:8000 -t public
```

3. Abre:

```text
http://localhost:8000
```

Aunque la URL sea `localhost`, la base que usa será la remota.

## 3) XAMPP / WAMP / CAMP

En XAMPP lo mismo:

- local = usa [.env.local](.env.local) con `localhost`
- producción = usa [.env](.env) con la base remota, pero igual abres la app en localhost

Paso a paso:

1. Pega la carpeta del proyecto en `htdocs`.
2. Si es local, usa la base local en [.env.local](.env.local).
3. Si es producción local, usa [.env](.env) con la base remota.
4. Abre la app en:

```text
http://localhost/nombre-del-proyecto/public
```

La clave es esta:

- la URL puede seguir siendo localhost
- lo que cambia es la conexión a la base de datos

## 4) Importante

- local = localhost + base local
- producción = localhost + base remota
- no subas `.env` ni `.env.local` con secretos reales
- si cambias de base, reinicia el servidor
