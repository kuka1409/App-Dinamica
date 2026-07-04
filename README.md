# TaskManager saneado con usuarios y roles

Proyecto limpio para ejecutar en Codespaces o en local con Docker.

## Qué corrige esta versión

- Las páginas visuales están en `app/paginas/`.
- Los endpoints JSON están únicamente en `app/api/*.php`.
- No hay endpoints duplicados fuera de `app/api`.
- Los nombres nuevos están en español.
- Se mantiene la idea visual original y solo se agregan estilos necesarios para usuarios, navegación superior y formularios centrados.
- Cada tarea pertenece al usuario que la creó.
- Cada subtarea queda protegida a través de la tarea principal.
- Los roles disponibles son `usuario` y `auditor`.
- La página `auditoria.php` y el endpoint `api/listar_logs.php` solo pueden ser usados por auditores.
- Se registra auditoría de creación, actualización, eliminación, lectura, inicio de sesión y cierre de sesión.

## Comandos

```bash
cp .env.example .env
docker compose up -d --build
```

Abrir la aplicación en el puerto configurado, por defecto:

```text
http://localhost:8080
```

En Codespaces debes abrir el puerto `8080`.

## Inicio limpio de base de datos

Si vienes de una versión anterior o una base rota, usa:

```bash
docker compose down -v
docker compose up -d --build
```

Esto elimina el volumen anterior de MySQL y crea la base nueva desde `db/init.sql`.


## Cuenta auditora inicial

La base creada desde `db/init.sql` incluye una cuenta auditora inicial para revisar la capa de logs:

```text
Correo: auditor@taskmanager.local
Contraseña: Auditor123
```

Los usuarios registrados desde la pantalla pública se crean siempre con rol `usuario`.

## Estructura principal

```text
app/
  api/
    comun.php
    registrar_usuario.php
    iniciar_sesion.php
    cerrar_sesion.php
    usuario_actual.php
    crear_tarea.php
    listar_tareas.php
    obtener_tarea.php
    actualizar_tarea.php
    eliminar_tarea.php
    crear_subtarea.php
    listar_subtareas.php
    obtener_subtarea.php
    actualizar_subtarea.php
    eliminar_subtarea.php
    cambiar_estado_subtarea.php
    listar_logs.php
  assets/
    css/styles.css
    js/            # módulos JavaScript separados por responsabilidad
    img/imagen-inicio.webp
  includes/
    funciones.php
    encabezado.php
    pie.php
  index.php
  conexion.php
  paginas/
    tareas_crear.php
    tareas_listar.php
    tareas_actualizar.php
    tareas_eliminar.php
    subtareas_crear.php
    subtareas_listar.php
    subtareas_actualizar.php
    subtareas_eliminar.php
    auditoria.php

db/
  init.sql
```


## Organización del JavaScript

El frontend está separado en módulos dentro de `app/assets/js/`: sesión, datos, selectores, formularios, listados, auditoría, utilidades y el inicializador `app.js`.
