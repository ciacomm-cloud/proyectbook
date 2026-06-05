# Sistema ODT Proyectbook — Documentación Técnica
**Fecha:** Junio 2026  
**Servidor:** Contabo VPS — Ubuntu 24.04 LTS  
**IP:** 89.117.148.158  
**Panel:** CloudPanel — https://panel.ciacomm.com  

---

## Arquitectura General

Sistema de Órdenes de Trabajo (ODT) multi-tenant. Cada cliente opera en su propio subdominio con su propia base de datos y su propia instancia de WhatsApp.

| Subdominio | BD | WPPConnect Port | Usuario sistema |
|---|---|---|---|
| master.proyectbook.com | proyectbookdb | 3030 | proyectbook-master |
| biomaa.proyectbook.com | biomaaproyect | 3031 | proyectbook-biomaa |

---

## Stack Tecnológico

- **Backend:** PHP 8.x con MySQLi
- **Frontend:** Bootstrap 5.3 + Bootstrap Icons
- **Base de datos:** MySQL/MariaDB
- **Email:** PHPMailer v7.1.1 via SMTP
- **WhatsApp:** WPPConnect (Node.js) + PM2
- **Control de versiones:** Git + GitHub (ciacomm-cloud/proyectbook)

---

## Estructura de Archivos

```
htdocs/[subdominio]/
├── index.php          — Login
├── dashboard.php      — Aplicación completa (~2,100 líneas)
├── conexion.php       — Credenciales de BD
├── logout.php         — Cerrar sesión
├── phpmailer/         — PHPMailer via Composer
│   └── vendor/        — Dependencias (no versionado)
└── uploads/           — Archivos subidos por usuarios
```

---

## Base de Datos — Tablas

| Tabla | Descripción |
|---|---|
| `usuarios` | id, nombre, usuario, password, email, celular, rol, avatar, whatsapp_apikey |
| `odts` | id, creador_id, departamento_id, tema, descripcion, archivo, prioridad, fecha_creacion, fecha_terminacion, estatus |
| `evidencias` | id, odt_id, usuario_id, comentario, archivo, fecha_hora |
| `participantes` | odt_id, usuario_id |
| `departamentos` | id, nombre |

---

## Reglas de Negocio Implementadas

- Solo usuarios `regular` crean ODTs
- Solo el creador puede cerrar su ODT
- Nadie puede editar ni borrar comentarios (historial inmutable)
- Admin es solo lectura: ve todo, no modifica nada de contenido
- Admin gestiona usuarios y departamentos únicamente
- Participantes: solo usuarios regulares (admin excluido)
- Al crear ODT: email + WhatsApp a todos los participantes
- Al comentar: email + WhatsApp a todos los involucrados excepto quien comenta
- Al agregar participantes desde ODT abierta: email + WhatsApp de bienvenida

---

## Configuración SMTP

- **Host:** mail.ciacomm.com  
- **Puerto:** 587 (STARTTLS)  
- **Usuario:** aviso@proyectbook.com  
- **From:** Sistema ODT Proyectbook  

---

## WhatsApp — WPPConnect

### Instalación
```
/home/claudeproyectbook/wppconnect/          — master.proyectbook.com (puerto 3030)
/home/claudeproyectbook/wppconnect-biomaa/   — biomaa.proyectbook.com (puerto 3031)
```

### API Keys internas
- master: `odt-proyectbook-2026`
- biomaa: `odt-biomaa-2026`

### Comandos PM2
```bash
# Ver procesos
/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 list

# Reiniciar
/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 restart wppconnect-odt
/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 restart wppconnect-biomaa

# Ver logs
/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 logs wppconnect-odt --lines 30

# Guardar estado (después de cambios)
/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 save
```

### Conectar WhatsApp (desde panel admin)
1. Conseguir chip de prepago (~$50 MXN)
2. Activar WhatsApp en ese número
3. Dashboard → pestaña WHATSAPP → escanear QR
4. El sistema queda conectado permanentemente

### PM2 como servicio del sistema
Configurado como servicio systemd — arranca automáticamente al reiniciar el VPS.
```
/etc/systemd/system/pm2-claudeproyectbook.service
```

---

## Duplicar para Nuevo Cliente

1. **CloudPanel:** crear sitio `[cliente].proyectbook.com`, usuario `proyectbook-[cliente]`
2. **Cloudflare:** agregar registro A → 89.117.148.158 (DNS only)
3. **Copiar archivos:**
```bash
cp -r /home/proyectbook-master/htdocs/master.proyectbook.com/. /home/proyectbook-[cliente]/htdocs/[cliente].proyectbook.com/
chown -R proyectbook-[cliente]:proyectbook-[cliente] /home/proyectbook-[cliente]/htdocs/[cliente].proyectbook.com/
```
4. **Copiar BD:**
```bash
mysqldump -u dbproyectbook -p proyectbookdb | mysql -u [usuario_bd] -p [nombre_bd]
```
5. **Actualizar conexion.php** con nuevas credenciales de BD
6. **Actualizar dashboard.php** — URLs y puerto WPPConnect
7. **Nueva instancia WPPConnect** en puerto siguiente disponible (3032, 3033...)
8. **PM2 save** para persistir

---

## GitHub

- **Repo:** https://github.com/ciacomm-cloud/proyectbook  
- **Branch:** master  

```bash
# Commitear cambios desde root en el servidor
cd /home/proyectbook-master/htdocs/master.proyectbook.com
git add dashboard.php
git commit -m "descripción"
git push
```

---

## Pendientes

- [ ] Conseguir chip prepago para WhatsApp de master.proyectbook.com
- [ ] Conseguir chip prepago para WhatsApp de biomaa.proyectbook.com
- [ ] Ejecutar PM2 save después de conectar WhatsApp en cada instancia
- [ ] Limpiar datos de prueba en biomaa (ODTs y usuarios de test)
- [ ] Desactivar display_errors en producción (dashboard.php línea 5-9)

---

## Notas de Seguridad

- `conexion.php` contiene credenciales de BD — está en .gitignore
- El servidor WPPConnect escucha en 0.0.0.0 — cerrar puerto 3030/3031 en UFW cuando no se use para QR
- Sin tokens CSRF (pendiente implementar)
- Sin rate limiting en login (pendiente implementar)
