# dbravoan/dbcms-module-forms 1.0.0

## Qué hace

`dbravoan/dbcms-module-forms` posee definiciones, campos y envíos de formularios.
Instala/activa `forms`, crea el formulario y configura idiomas, campos y validación.
`widget_form` con `form_slug` funciona en secciones y páginas Blade registradas.

Los formularios públicos conservan CSRF/sesión y honeypot. Configura tratamiento
`forms`, retención finita y aviso contextual antes de recoger datos. El marketing
opcional es independiente. No compartas HTML final de formularios entre visitantes
ni recojas IP/user-agent innecesarios.

Las operaciones de derechos autorizadas usan IDs explícitos:

```bash
./vendor/bin/sail artisan forms:privacy export --id=SUBMISSION_ID --verified
./vendor/bin/sail artisan forms:privacy erase --id=SUBMISSION_ID --verified
./vendor/bin/sail artisan forms:privacy prune
```

`--verified` confirma la revisión previa de identidad, alcance y retención del
operador; no la hace. Los exports son privados y se eliminan tras atender la
solicitud. La limpieza también aplica a envíos retenidos después de desactivar.
Declara `retention_days` positivo en `legal.processing` con propietario `forms`;
el plazo empieza en el envío. El scheduler ejecuta limpieza diaria. Consulta el
[contrato de extensiones](../../../docs/EXTENSIONS.md).

## Requisitos y dependencias

- `dbravoan/dbcms-core` `^1.0`
- Identificador CMS: `forms`

## Instalación y activación

Instala el paquete con Composer e instálalo/actívalo en el CMS (`forms`).

## Configuración

Ninguno en `module.json`. La configuración vive en las definiciones de cada formulario en el panel.

## Rutas

Admin de formularios y envío público (widget/form).

## Comandos Artisan

- `forms:privacy` — firma: `forms:privacy {action : export, erase or prune} {--id=* : Explicit submission IDs} {--verified : Identity, scope and retention restrictions have been checked}`

## Permisos

- `manage:forms`

## Privacidad y datos

Consulta el contrato de extensiones y los tratamientos `legal.processing` aplicables.

## Enlaces

- [Extensiones](../../../docs/EXTENSIONS.md)
- [Núcleo y extensiones](../../../specs/CORE_AND_EXTENSIONS.md)
- [README del núcleo](../../../packages/core/README.md)
- [README del monorepo](../../../README.md)
