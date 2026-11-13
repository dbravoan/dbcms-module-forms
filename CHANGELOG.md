# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.1.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [Sin publicar]

## [1.1.0] - 2026-11-13

### Añadido

- Fecha, hora, URL, ocultos y adjuntos privados; diseño por pasos, grupos y columnas sin duplicar campos.
- Acciones por envío con entrega tras commit, reintentos acotados y receptores autorizados.
- Referencias públicas filtradas y paginadas; previsualización sin envíos.


## [1.0.1] - 2026-11-12

### Corregido

- Las traducciones de formularios y campos respetan el idioma y mercado solicitados mediante el lector público compartido.
- La dependencia mínima de core pasa a `^1.1` por las APIs compartidas que utiliza este paquete.

## [1.0.0] - 2026-11-05

### Añadido

- Primera versión pública.
- `dbravoan/dbcms-module-forms` posee definiciones, campos y envíos de formularios. Instala/activa `forms`, crea el formulario y configura idiomas, campos y validación. `widget_form` con `form_slug` funciona en…
