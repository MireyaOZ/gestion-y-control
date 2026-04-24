# Mapa De Relaciones Del Proyecto

Este documento resume qué archivos trabajan juntos en cada módulo del sistema.

La idea es responder rápido preguntas como:

- qué controlador gobierna un modelo
- qué vistas pertenecen a ese flujo
- qué componentes Blade reutiliza
- qué políticas, servicios o archivos compartidos intervienen

## Punto De Entrada

- Rutas principales autenticadas: `routes/web.php`
- Rutas de autenticación: `routes/auth.php`
- Helpers Alpine y comportamiento UI compartido: `resources/js/app.js`
- Layout principal autenticado: `resources/views/layouts/navigation.blade.php`
- Modal compartido: `resources/views/components/modal.blade.php`

## Modulo De Tareas

| Pieza | Archivos ligados |
| --- | --- |
| Modelo principal | `app/Models/Task.php` |
| Controlador | `app/Http/Controllers/TaskController.php` |
| Politica | `app/Policies/TaskPolicy.php` |
| Rutas | `tasks.*` en `routes/web.php` |
| Vistas principales | `resources/views/tasks/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`, `_form.blade.php` |
| Reportes | `report-pdf.blade.php`, `report-excel.blade.php`, `report-table.blade.php`, `report-list.blade.php`, `hierarchy-report-pdf.blade.php`, `hierarchy-report-table.blade.php`, `hierarchy-report-node.blade.php`, `hierarchy-report-excel.blade.php`, `hierarchy-report-excel-table.blade.php`, `hierarchy-report-excel-list.blade.php` |
| Componentes usados | `x-app-layout`, `x-status-pill`, `x-search-select`, `x-search-multi-select`, `x-modal` |
| Vistas compartidas usadas | `resources/views/shared/resource-panels.blade.php`, `change-log-panel.blade.php`, `comments-section.blade.php` |
| Modelos catalogo ligados | `app/Models/TaskStatus.php`, `Priority.php`, `User.php` |
| Modelo relacionado | `app/Models/Subtask.php` |

### Relaciones principales del modulo de tareas

- `TaskController@index` arma el listado y devuelve `resources/views/tasks/index.blade.php`.
- `TaskController@create` y `@edit` reutilizan `resources/views/tasks/_form.blade.php`.
- `TaskController@show` devuelve `resources/views/tasks/show.blade.php` y desde ahí cuelgan:
  - subtareas raíz con `resources/views/subtasks/tree-node.blade.php`
  - recursos con `resources/views/shared/resource-panels.blade.php`
  - historial con `resources/views/shared/change-log-panel.blade.php`
  - comentarios con `resources/views/shared/comments-section.blade.php`
- `TaskController@report` usa las vistas `report-*`.
- `TaskController@hierarchyReport` usa las vistas `hierarchy-report-*`.
- `Task.php` tiene la relación hacia `rootSubtasks`, `subtasks`, `status`, `priority`, `creator` y `assignees`.
- `TaskPolicy.php` controla ver, crear, editar, eliminar, asignar, cambiar estatus, comentar y manejar recursos.

## Modulo De Subtareas

| Pieza | Archivos ligados |
| --- | --- |
| Modelo principal | `app/Models/Subtask.php` |
| Controlador | `app/Http/Controllers/SubtaskController.php` |
| Politica | `app/Policies/SubtaskPolicy.php` |
| Rutas | `subtasks.*` en `routes/web.php` |
| Vistas principales | `resources/views/subtasks/create.blade.php`, `edit.blade.php`, `show.blade.php`, `_form.blade.php`, `tree-node.blade.php` |
| Componentes usados | `x-app-layout`, `x-status-pill`, `x-search-multi-select` |
| Vistas compartidas usadas | `resources/views/shared/resource-panels.blade.php`, `change-log-panel.blade.php`, `comments-section.blade.php` |
| Modelos catalogo ligados | `app/Models/TaskStatus.php`, `Priority.php`, `Task.php`, `User.php` |
| Modelo padre opcional | `app/Models/Subtask.php` como `parentSubtask` |

### Relaciones principales del modulo de subtareas

- `SubtaskController@create` y `@edit` reutilizan `resources/views/subtasks/_form.blade.php`.
- `SubtaskController@show` devuelve `resources/views/subtasks/show.blade.php`.
- `resources/views/subtasks/show.blade.php` reutiliza `resources/views/subtasks/tree-node.blade.php` para el arbol recursivo.
- `Subtask.php` depende de:
  - `task()` para saber a qué tarea pertenece
  - `parentSubtask()` y `childSubtasks()` para la jerarquía
  - `status()`, `priority()`, `creator()`, `assignees()` para catálogos y responsables
- `SubtaskPolicy.php` agrega la regla importante `createChild`, que controla si una subtarea puede anidar hijas.

## Modulo De Correos

| Pieza | Archivos ligados |
| --- | --- |
| Modelo principal | `app/Models/EmailRequest.php` |
| Controlador | `app/Http/Controllers/EmailRequestController.php` |
| Rutas | `emails.*` en `routes/web.php` |
| Vista principal | `resources/views/emails/index.blade.php` |
| Reportes | `resources/views/emails/report-pdf.blade.php`, `report-excel.blade.php`, `report-table.blade.php`, `history-report-pdf.blade.php`, `history-report-excel.blade.php`, `history-report-table.blade.php` |
| Componentes usados | `x-app-layout`, `x-modal`, `x-status-pill` |
| Vistas compartidas usadas | historial inline dentro de `emails/index.blade.php` |
| Modelos catalogo ligados | `app/Models/EmailCargo.php`, `EmailMovementType.php`, `User.php` |

### Relaciones principales del modulo de correos

- `EmailRequestController@index` devuelve `resources/views/emails/index.blade.php`.
- El mismo `index.blade.php` contiene:
  - listado
  - filtros
  - modal de alta
  - modal de edición
  - modal de historial
- `EmailRequest.php` depende de:
  - `cargo()` hacia `EmailCargo`
  - `movementType()` hacia `EmailMovementType`
  - `creator()` hacia `User`
- El estado visual de un correo no sale de una tabla propia, sino de accessors en `EmailRequest.php`:
  - `operational_status`
  - `operational_status_tone`

## Modulo De Sistemas

| Pieza | Archivos ligados |
| --- | --- |
| Modelo principal | `app/Models/SystemRecord.php` |
| Controlador | `app/Http/Controllers/SystemRecordController.php` |
| Rutas | `systems.*` en `routes/web.php` |
| Vista principal | `resources/views/systems/index.blade.php` |
| Reportes | `resources/views/systems/report-pdf.blade.php`, `report-excel.blade.php`, `report-table.blade.php`, `history-report-pdf.blade.php`, `history-report-excel.blade.php`, `history-report-table.blade.php` |
| Componentes usados | `x-app-layout`, `x-modal` |
| Vistas compartidas usadas | historial inline dentro de `systems/index.blade.php` |
| Modelos catalogo ligados | `app/Models/SystemStatus.php`, `User.php` |

### Relaciones principales del modulo de sistemas

- `SystemRecordController@index` devuelve `resources/views/systems/index.blade.php`.
- Igual que correos, el `index.blade.php` concentra:
  - listado
  - filtros
  - modal de alta
  - modal de edición
  - modal de historial
- `SystemRecord.php` depende de:
  - `status()` hacia `SystemStatus`
  - `creator()` hacia `User`
- Los contadores de Trello se manejan en el modelo y en la UI con:
  - accessor `total_trello_cards` en `SystemRecord.php`
  - helper Alpine `systemMetricsForm()` en `resources/js/app.js`

## Modulo De Recursos Compartidos

Este bloque conecta tareas, subtareas y sistemas con adjuntos, links, comentarios e historial.

| Pieza | Archivos ligados |
| --- | --- |
| Trait central | `app/Models/Concerns/HasResources.php` |
| Controlador adjuntos | `app/Http/Controllers/AttachmentController.php` |
| Controlador links | `app/Http/Controllers/LinkController.php` |
| Controlador comentarios | `app/Http/Controllers/CommentController.php` |
| Resolución polimórfica | `app/Http/Controllers/Concerns/ResolvesManagedModels.php` |
| Modelos recurso | `app/Models/Attachment.php`, `ResourceLink.php`, `Comment.php`, `ChangeLog.php` |
| Vistas compartidas | `resources/views/shared/resource-panels.blade.php`, `comments-section.blade.php`, `change-log-panel.blade.php` |
| Modelos que lo usan | `Task.php`, `Subtask.php`, `EmailRequest.php`, `SystemRecord.php` |

### Cómo se conectan

- `HasResources.php` agrega a cada modelo las relaciones:
  - `attachments()`
  - `links()`
  - `comments()`
  - `changeLogs()`
- `AttachmentController`, `LinkController` y `CommentController` no trabajan con un modelo fijo; resuelven el modelo por tipo e id.
- `ResolvesManagedModels.php` hoy reconoce estos tipos:
  - `task`
  - `subtask`
  - `system`
- Eso significa que los paneles compartidos están ligados directamente a tareas, subtareas y sistemas. No a `emailRequest` en este trait.

## Servicio De Historial

| Pieza | Archivos ligados |
| --- | --- |
| Servicio | `app/Services/ChangeLogger.php` |
| Modelo historial | `app/Models/ChangeLog.php` |
| Vistas que lo muestran | `resources/views/shared/change-log-panel.blade.php`, historiales inline de correos y sistemas |

### Función del servicio

- `ChangeLogger::log()` crea entradas en `change_logs` para el modelo recibido.
- Si el modelo es una `Subtask`, también replica el evento en la `Task` padre con `mirrorSubtaskLogToTask()`.

## Modulo De Busqueda De Usuarios

| Pieza | Archivos ligados |
| --- | --- |
| Controlador | `app/Http/Controllers/SearchController.php` |
| Ruta | `search.users` en `routes/web.php` |
| Componentes consumidores | `resources/views/components/search-select.blade.php`, `search-multi-select.blade.php` |
| Vistas que lo usan | `tasks/index.blade.php`, `tasks/_form.blade.php`, `subtasks/_form.blade.php` |

## Dashboard

| Pieza | Archivos ligados |
| --- | --- |
| Controlador | `app/Http/Controllers/DashboardController.php` |
| Vista | `resources/views/dashboard.blade.php` |
| Modelos consultados | `Task.php`, `EmailRequest.php`, `SystemRecord.php` |

## Perfil Y Cuenta

| Pieza | Archivos ligados |
| --- | --- |
| Controlador | `app/Http/Controllers/ProfileController.php` |
| Vista principal | `resources/views/profile/edit.blade.php` |
| Partials | `resources/views/profile/partials/update-profile-information-form.blade.php`, `update-password-form.blade.php`, `delete-user-form.blade.php` |
| Componentes usados | `x-app-layout`, `x-input-label`, `x-input-error`, `x-modal`, `x-flash-status` |
| Modelo ligado | `app/Models/User.php` |

## Autenticacion

| Pieza | Archivos ligados |
| --- | --- |
| Rutas | `routes/auth.php` |
| Controladores | `app/Http/Controllers/Auth/*` |
| Vistas | `resources/views/auth/login.blade.php`, `register.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`, `confirm-password.blade.php`, `verify-email.blade.php` |
| Modelo ligado | `app/Models/User.php` |
| Componentes usados | `x-guest-layout`, `x-auth-session-status`, `x-validation-errors`, `x-input-error` |

### Relaciones principales de auth

- `AuthenticatedSessionController` usa `auth/login.blade.php`.
- `RegisteredUserController` usa `auth/register.blade.php`.
- `PasswordResetLinkController` usa `auth/forgot-password.blade.php`.
- `NewPasswordController` usa `auth/reset-password.blade.php`.
- `ConfirmablePasswordController` usa `auth/confirm-password.blade.php`.
- `EmailVerificationPromptController` usa `auth/verify-email.blade.php`.

## Administracion

| Pieza | Archivos ligados |
| --- | --- |
| Controladores | `app/Http/Controllers/Admin/UserController.php`, `RoleController.php`, `PermissionController.php` |
| Rutas | `admin.users.*`, `admin.roles.*`, `admin.permissions.*` en `routes/web.php` |
| Vistas | `resources/views/admin/users/*`, `admin/roles/*`, `admin/permissions/*` |
| Modelo principal usuarios | `app/Models/User.php` |
| Modelos de Spatie | `Spatie\Permission\Models\Role`, `Permission` |
| Archivo de apoyo | `app/Support/PermissionCatalog.php` |

### Relaciones principales de administracion

- `UserController` gobierna el CRUD de usuarios y la impersonación.
- `RoleController` gobierna el CRUD de roles y asignación de permisos.
- `PermissionController` gobierna el CRUD de permisos.
- `User.php` usa `HasRoles` de Spatie, así que administración y permisos están unidos a ese modelo.

## Componentes Blade Compartidos

| Componente | Quién lo usa |
| --- | --- |
| `resources/views/components/modal.blade.php` | modales de tareas, correos, sistemas, perfil y paneles compartidos |
| `status-pill.blade.php` | tareas, subtareas, correos, sistemas |
| `search-select.blade.php` | filtros de tareas |
| `search-multi-select.blade.php` | formularios de tareas y subtareas |
| `flash-status.blade.php` | formularios de perfil y notificaciones pequeñas |
| `dropdown.blade.php`, `dropdown-link.blade.php`, `nav-link.blade.php`, `responsive-nav-link.blade.php` | navegación general |
| `input-label.blade.php`, `input-error.blade.php`, `text-input.blade.php`, `validation-errors.blade.php`, `auth-session-status.blade.php` | auth, perfil y administración |

## Helpers Alpine En UI

Archivo central: `resources/js/app.js`

| Helper | Quién lo usa |
| --- | --- |
| `modalDialog()` | `resources/views/components/modal.blade.php` |
| `passwordField()` | auth, perfil, admin usuarios |
| `navigationMenu()` | `resources/views/layouts/navigation.blade.php` |
| `togglePanel()` | `resources/views/components/dropdown.blade.php` |
| `treeNodeToggle()` | `resources/views/subtasks/tree-node.blade.php` |
| `timedVisibility()` | flashes y mensajes temporales |
| `systemMetricsForm()` | formularios de sistemas |
| `filterDrawer()` | filtros de tareas, correos, sistemas |
| `expandableList()` | listas expandibles de tareas y subtareas |
| `reportOptions()` | modal de exportación jerárquica de tareas |

## Catalogos Y Modelos De Apoyo

| Modelo | Pertenece principalmente a |
| --- | --- |
| `app/Models/TaskStatus.php` | tareas y subtareas |
| `app/Models/Priority.php` | tareas y subtareas |
| `app/Models/SystemStatus.php` | sistemas |
| `app/Models/EmailCargo.php` | correos |
| `app/Models/EmailMovementType.php` | correos |

## Archivos Legacy O Superficie Residual

- Existe carpeta `resources/views/projects/`, pero el módulo de proyectos ya no aparece conectado en `routes/web.php`.
- Si se quiere limpiar más el proyecto, esa carpeta es candidata a revisión porque hoy no forma parte del flujo principal expuesto por rutas.

## Guía Rápida: Si Quieres Cambiar X, Revisa Y

| Si quieres cambiar... | Revisa primero... |
| --- | --- |
| El listado o filtros de tareas | `TaskController.php` + `resources/views/tasks/index.blade.php` |
| El formulario de tarea | `TaskController.php` + `resources/views/tasks/_form.blade.php` |
| El detalle de una tarea | `TaskController.php` + `resources/views/tasks/show.blade.php` |
| El árbol de subtareas | `Subtask.php` + `resources/views/subtasks/tree-node.blade.php` + `resources/views/subtasks/show.blade.php` |
| Crear subtareas hijas | `SubtaskPolicy.php` + `SubtaskController.php` + `resources/views/subtasks/show.blade.php` |
| Correos y sus filtros | `EmailRequestController.php` + `resources/views/emails/index.blade.php` |
| Sistemas y métricas de pruebas | `SystemRecordController.php` + `SystemRecord.php` + `resources/views/systems/index.blade.php` + `resources/js/app.js` |
| Adjuntos, links o comentarios | `HasResources.php` + `AttachmentController.php` / `LinkController.php` / `CommentController.php` + `resources/views/shared/*` |
| Reglas de permisos sobre tareas | `TaskPolicy.php` |
| Reglas de permisos sobre subtareas | `SubtaskPolicy.php` |
| Roles y permisos | `app/Support/PermissionCatalog.php` + `app/Http/Controllers/Admin/*` |
| Modales o estados Alpine reutilizados | `resources/views/components/modal.blade.php` + `resources/js/app.js` |
