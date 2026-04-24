# Plataforma de Gestion y Seguimiento

Este proyecto esta configurado para ejecutarse localmente con Laragon.

## Configuracion local esperada

- PHP 8.3 o superior
- MySQL corriendo desde Laragon
- Composer
- Node.js y npm

La configuracion por defecto del proyecto usa estos valores locales:

- URL: `http://gestion-y-control.test`
- DB host: `127.0.0.1`
- DB port: `3306`
- DB name: `Plataforma_de_Gestion_y_Seguimiento`
- DB user: `root`
- DB password: vacio

## Arranque con Laragon

1. Inicia Apache y MySQL desde Laragon.
2. Crea la base de datos `Plataforma_de_Gestion_y_Seguimiento` si aun no existe.
3. Instala dependencias:

```bash
composer install
npm install
```

4. Genera la clave si hace falta:

```bash
php artisan key:generate
```

5. Ejecuta migraciones:

```bash
php artisan migrate --seed
```

6. Levanta Vite en desarrollo:

```bash
npm run dev
```

Con Laragon, la aplicacion debe responder en `http://gestion-y-control.test`.

Si usas `make`, los comandos por defecto ahora son locales:

```bash
make install
make migrate
make seed
make test
make dev
```
