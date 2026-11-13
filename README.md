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

## Edición, disposición y acciones

La definición del formulario sigue siendo la única fuente de campos y validación.
Además de los tipos existentes admite fecha, hora, URL, ocultos y archivos. El
diseño referencia cada clave de campo exactamente una vez: pasos, grupos y columnas
por móvil/tablet/escritorio. El bloque `widget_form` selecciona el formulario; no
copia sus reglas. El enlace de edición contextual requiere `manage:forms`.

Las acciones configurables son confirmación, redirección interna, correo, webhook
y apertura/cierre de overlay. Se guardan con identificadores estables; los efectos
externos se entregan después del commit mediante registros por envío/acción, con
un máximo de tres intentos. El scheduler recupera entregas pendientes con
`forms:deliver`; un fallo de un destino no repite las otras acciones ni crea otro
registro de envío. Un receptor remoto debe deduplicar con la clave de idempotencia
recibida: ningún sistema puede prometer exactamente una recepción si el destino
procesa una petición y pierde su respuesta.

Los webhooks se declaran en `forms_delivery.webhooks` desde configuración del
servidor: identificador, URL HTTPS, campos permitidos y credenciales. El editor
solo elige el identificador. Cada receptor necesita el tratamiento revisado
`forms_webhook_<identificador>`; una finalidad opcional requiere consentimiento.
Las credenciales no entran en el payload ni en snapshots. Un webhook es un destino
genérico, no una integración CRM completa.

Los adjuntos se validan en servidor y se guardan en almacenamiento privado;
solo el permiso del formulario permite descargarlos. Exportación, borrado y
conservación cubren los archivos y acciones asociados. Las previsualizaciones no
envían formularios ni ejecutan acciones. CAPTCHA, honeypot, CSRF y validación
completa siguen aplicándose a los envíos públicos.

## Requisitos y dependencias

- `dbravoan/dbcms-core` `^1.2`
- Identificador CMS: `forms`

## Instalación y activación

Instala el paquete con Composer e instálalo/actívalo en el CMS (`forms`).

## Configuración

Las definiciones y disposición se editan en el panel. Los receptores autorizados viven en `forms_delivery.webhooks` del servidor; consulta la sección de acciones.

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
