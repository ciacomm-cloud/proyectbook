<?php

// Rastreador de errores para aislar problemas

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



// --- MAILER ---

require 'phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;



function enviar_correo(array $destinatarios, string $asunto, string $cuerpo_html): bool {

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host       = 'mail.ciacomm.com';

        $mail->SMTPAuth   = true;

        $mail->Username   = 'aviso@proyectbook.com';

        $mail->Password   = '43,sToRhY,}';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port       = 587;

        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('aviso@proyectbook.com', 'Sistema ODT Proyectbook');

        foreach ($destinatarios as $d) {

            $mail->addAddress($d['email'], $d['nombre']);

        }

        $mail->isHTML(true);

        $mail->Subject = $asunto;

        $mail->Body    = $cuerpo_html;

        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $cuerpo_html));

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log("Mailer Error: " . $mail->ErrorInfo);

        return false;

    }

}



function html_nueva_odt(string $creador, string $tema, string $descripcion, string $prioridad, string $fecha_vence, int $odt_id): string {

    $url = "https://master.proyectbook.com/dashboard.php?view_odt={$odt_id}";

    $color_prio = match(strtolower($prioridad)) {

        'urgente' => '#dc3545', 'alta' => '#fd7e14', 'media' => '#0d6efd', default => '#6c757d',

    };

    return "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#111;padding:20px 24px;border-radius:8px 8px 0 0;'>
            <h2 style='color:#fff;margin:0;font-size:18px;'>Nueva Orden de Trabajo asignada</h2>
        </div>
        <div style='background:#fff;padding:24px;border:1px solid #e0e0e0;border-top:none;'>
            <p style='margin:0 0 16px;color:#333;'>Fuiste asignado como participante en una nueva ODT:</p>
            <table style='width:100%;border-collapse:collapse;margin-bottom:20px;'>
                <tr><td style='padding:8px 12px;background:#f8f9fa;color:#555;font-size:13px;width:130px;'>ODT #</td><td style='padding:8px 12px;font-weight:bold;'>{$odt_id}</td></tr>
                <tr><td style='padding:8px 12px;background:#f8f9fa;color:#555;font-size:13px;'>Tema</td><td style='padding:8px 12px;font-weight:bold;'>" . htmlspecialchars($tema) . "</td></tr>
                <tr><td style='padding:8px 12px;background:#f8f9fa;color:#555;font-size:13px;'>Creada por</td><td style='padding:8px 12px;'>" . htmlspecialchars($creador) . "</td></tr>
                <tr><td style='padding:8px 12px;background:#f8f9fa;color:#555;font-size:13px;'>Prioridad</td><td style='padding:8px 12px;'><span style='background:{$color_prio};color:#fff;padding:3px 10px;border-radius:12px;font-size:13px;'>{$prioridad}</span></td></tr>
                <tr><td style='padding:8px 12px;background:#f8f9fa;color:#555;font-size:13px;'>Vencimiento</td><td style='padding:8px 12px;'>" . date('d/m/Y', strtotime($fecha_vence)) . "</td></tr>
            </table>
            <div style='background:#f8f9fa;border-left:4px solid #111;padding:12px 16px;margin-bottom:24px;border-radius:0 6px 6px 0;'>
                <p style='margin:0;color:#333;font-size:14px;white-space:pre-wrap;'>" . htmlspecialchars($descripcion) . "</p>
            </div>
            <a href='{$url}' style='display:inline-block;background:#111;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;'>Ver ODT en el sistema</a>
        </div>
        <div style='padding:12px 24px;background:#f0f0f0;border-radius:0 0 8px 8px;'>
            <p style='margin:0;font-size:11px;color:#888;'>Este es un mensaje automático del Sistema ODT Proyectbook.</p>
        </div>
    </div>";

}



function html_nuevo_comentario(string $autor, string $tema, string $comentario, int $odt_id): string {

    $url = "https://master.proyectbook.com/dashboard.php?view_odt={$odt_id}";

    return "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#111;padding:20px 24px;border-radius:8px 8px 0 0;'>
            <h2 style='color:#fff;margin:0;font-size:18px;'>Nuevo avance en ODT #{$odt_id}</h2>
        </div>
        <div style='background:#fff;padding:24px;border:1px solid #e0e0e0;border-top:none;'>
            <p style='margin:0 0 4px;color:#555;font-size:13px;'>ODT: <strong>" . htmlspecialchars($tema) . "</strong></p>
            <p style='margin:0 0 20px;color:#555;font-size:13px;'><strong>" . htmlspecialchars($autor) . "</strong> registró un nuevo avance:</p>
            <div style='background:#f8f9fa;border-left:4px solid #0d6efd;padding:12px 16px;margin-bottom:24px;border-radius:0 6px 6px 0;'>
                <p style='margin:0;color:#333;font-size:14px;white-space:pre-wrap;'>" . htmlspecialchars($comentario) . "</p>
            </div>
            <a href='{$url}' style='display:inline-block;background:#111;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;'>Ver ODT en el sistema</a>
        </div>
        <div style='padding:12px 24px;background:#f0f0f0;border-radius:0 0 8px 8px;'>
            <p style='margin:0;font-size:11px;color:#888;'>Este es un mensaje automático del Sistema ODT Proyectbook.</p>
        </div>
    </div>";

}



// --- WHATSAPP (CallMeBot) ---

function enviar_whatsapp(string $celular, string $apikey, string $mensaje): void {

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . urlencode($celular)
         . "&apikey=" . urlencode($apikey)
         . "&text=" . urlencode($mensaje);

    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);

    @file_get_contents($url, false, $ctx);

}

function notificar_whatsapp(array $usuarios, string $mensaje): void {

    foreach ($usuarios as $u) {

        if (!empty($u['celular']) && !empty($u['whatsapp_apikey'])) {

            enviar_whatsapp($u['celular'], $u['whatsapp_apikey'], $mensaje);

        }

    }

}



session_start();

require 'conexion.php';



// Protección de acceso

if (!isset($_SESSION['usuario_id'])) {

    header("Location: index.php");

    exit;

}



$mi_id = $_SESSION['usuario_id'];

$mi_rol = $_SESSION['rol'];

$mi_nombre = $_SESSION['nombre'];



// --- ACCIÓN: ACTUALIZAR AVATAR ---

if (isset($_POST['update_avatar'])) {

    $updates = [];

    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {

        $ext = pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION);

        $nombre_archivo = "avatar_" . $mi_id . "_" . time() . "." . $ext;

        if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], "uploads/" . $nombre_archivo)) {

            $updates[] = "avatar = '$nombre_archivo'";

        }

    }

    if (isset($_POST['wa_apikey'])) {

        $wa_key = $conexion->real_escape_string(trim($_POST['wa_apikey']));

        $updates[] = "whatsapp_apikey = " . ($wa_key !== '' ? "'$wa_key'" : "NULL");

    }

    if (!empty($updates)) {

        $conexion->query("UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = $mi_id");

    }

    header("Location: dashboard.php");

    exit;

}



// --- ACCIÓN: CREAR NUEVA ODT ---

if (isset($_POST['crear_odt']) && $mi_rol != 'admin') {

    $tema = $conexion->real_escape_string($_POST['tema']);

    $descripcion = $conexion->real_escape_string($_POST['descripcion']);

    $prioridad = $_POST['prioridad'];

    $departamento_id = intval($_POST['departamento_id']);

    $fecha_terminacion = $_POST['fecha_terminacion'];

    

    $archivo_db = "";

    if (isset($_FILES['odt_archivo']) && $_FILES['odt_archivo']['error'] == 0) {

        $archivo_db = "odt_" . time() . "_" . basename($_FILES['odt_archivo']['name']);

        move_uploaded_file($_FILES['odt_archivo']['tmp_name'], "uploads/" . $archivo_db);

    }

    

    $sql = "INSERT INTO odts (creador_id, departamento_id, tema, descripcion, archivo, prioridad, fecha_terminacion, estatus) 

            VALUES ($mi_id, $departamento_id, '$tema', '$descripcion', '$archivo_db', '$prioridad', '$fecha_terminacion', 'abierta')";

    

    if ($conexion->query($sql)) {

        $odt_id = $conexion->insert_id;

        $ids_participantes = [];

        if (isset($_POST['participantes']) && is_array($_POST['participantes'])) {

            foreach ($_POST['participantes'] as $p_id) {

                $p_id = intval($p_id);

                $conexion->query("INSERT INTO participantes (odt_id, usuario_id) VALUES ($odt_id, $p_id)");

                $ids_participantes[] = $p_id;

            }

        }

        // --- Notificar a participantes por email ---

        if (!empty($ids_participantes)) {

            $ids_str = implode(',', $ids_participantes);

            $res_part = $conexion->query("SELECT nombre, email, celular, whatsapp_apikey FROM usuarios WHERE id IN ($ids_str)");

            $destinatarios = [];

            $dest_wa = [];

            while ($rp = $res_part->fetch_assoc()) {

                if (!empty($rp['email']))          $destinatarios[] = ['email' => $rp['email'], 'nombre' => $rp['nombre']];

                if (!empty($rp['whatsapp_apikey'])) $dest_wa[]      = $rp;

            }

            if (!empty($destinatarios)) {

                $asunto = "Nueva ODT asignada: " . $tema;

                $cuerpo = html_nueva_odt($mi_nombre, $tema, $descripcion, $prioridad, $fecha_terminacion, $odt_id);

                enviar_correo($destinatarios, $asunto, $cuerpo);

            }

            if (!empty($dest_wa)) {

                $msg_wa = "📋 *Nueva ODT #{$odt_id} asignada*\n"
                        . "*{$tema}*\n"
                        . "Prioridad: {$prioridad} | Vence: " . date('d/m/Y', strtotime($fecha_terminacion)) . "\n"
                        . "Creada por: {$mi_nombre}\n"
                        . "👉 https://master.proyectbook.com/dashboard.php?view_odt={$odt_id}";

                notificar_whatsapp($dest_wa, $msg_wa);

            }

        }

        header("Location: dashboard.php?msg=odt_creada");

        exit;

    }

}



// --- ACCIÓN: AGREGAR RESPUESTA / CONTESTACIÓN ---

if (isset($_POST['enviar_respuesta'])) {

    $odt_id = intval($_POST['odt_id']);

    $comentario = $conexion->real_escape_string($_POST['comentario']);

    

    $odt_info = $conexion->query("SELECT creador_id, estatus FROM odts WHERE id = $odt_id")->fetch_assoc();

    $soy_creador = ($odt_info['creador_id'] == $mi_id);

    $soy_partic = $conexion->query("SELECT 1 FROM participantes WHERE odt_id = $odt_id AND usuario_id = $mi_id")->num_rows > 0;

    

    if ($odt_info['estatus'] == 'abierta' && ($soy_creador || $soy_partic) && $mi_rol != 'admin') {

        $archivo_db = "";

        if (isset($_FILES['resp_archivo']) && $_FILES['resp_archivo']['error'] == 0) {

            $archivo_db = "evid_" . time() . "_" . basename($_FILES['resp_archivo']['name']);

            move_uploaded_file($_FILES['resp_archivo']['tmp_name'], "uploads/" . $archivo_db);

        }

        $conexion->query("INSERT INTO evidencias (odt_id, usuario_id, comentario, archivo) VALUES ($odt_id, $mi_id, '$comentario', '$archivo_db')");

        $tema_odt = $conexion->query("SELECT tema FROM odts WHERE id = $odt_id")->fetch_assoc()['tema'];

        // --- Agregar nuevos participantes si se seleccionaron ---

        $ids_nuevos = [];

        if (isset($_POST['nuevos_participantes']) && is_array($_POST['nuevos_participantes'])) {

            foreach ($_POST['nuevos_participantes'] as $np_id) {

                $np_id = intval($np_id);

                $conexion->query("INSERT IGNORE INTO participantes (odt_id, usuario_id) VALUES ($odt_id, $np_id)");

                $ids_nuevos[] = $np_id;

            }

        }

        // --- Notificar nuevos participantes con email de bienvenida a la ODT ---

        if (!empty($ids_nuevos)) {

            $ids_str = implode(',', $ids_nuevos);

            $res_np = $conexion->query("SELECT nombre, email FROM usuarios WHERE id IN ($ids_str) AND email != ''");

            $dest_nuevos = [];

            while ($rnp = $res_np->fetch_assoc()) {

                $dest_nuevos[] = ['email' => $rnp['email'], 'nombre' => $rnp['nombre']];

            }

            if (!empty($dest_nuevos) || !empty($ids_nuevos)) {

                $odt_full = $conexion->query("SELECT descripcion, prioridad, fecha_terminacion FROM odts WHERE id = $odt_id")->fetch_assoc();

                if (!empty($dest_nuevos)) {

                    $asunto_np = "Fuiste agregado a una ODT: " . $tema_odt;

                    $cuerpo_np = html_nueva_odt($mi_nombre, $tema_odt, $odt_full['descripcion'], $odt_full['prioridad'], $odt_full['fecha_terminacion'], $odt_id);

                    enviar_correo($dest_nuevos, $asunto_np, $cuerpo_np);

                }

                // WhatsApp a nuevos participantes
                if (!empty($ids_nuevos)) {

                    $ids_str_np = implode(',', $ids_nuevos);

                    $res_wa_np = $conexion->query("SELECT celular, whatsapp_apikey FROM usuarios WHERE id IN ($ids_str_np) AND whatsapp_apikey IS NOT NULL");

                    $dest_wa_np = [];

                    while ($rwnp = $res_wa_np->fetch_assoc()) $dest_wa_np[] = $rwnp;

                    if (!empty($dest_wa_np)) {

                        $msg_wa_np = "📋 *Te agregaron a una ODT #{$odt_id}*\n"
                                   . "*{$tema_odt}*\n"
                                   . "Prioridad: {$odt_full['prioridad']} | Vence: " . date('d/m/Y', strtotime($odt_full['fecha_terminacion'])) . "\n"
                                   . "Por: {$mi_nombre}\n"
                                   . "👉 https://master.proyectbook.com/dashboard.php?view_odt={$odt_id}";

                        notificar_whatsapp($dest_wa_np, $msg_wa_np);

                    }

                }

            }

        }

        // --- Notificar al resto de involucrados (excepto quien comenta) sobre el nuevo avance ---

        $res_inv = $conexion->query("

            SELECT DISTINCT u.nombre, u.email, u.celular, u.whatsapp_apikey FROM usuarios u

            WHERE u.id != $mi_id AND (

                u.id = (SELECT creador_id FROM odts WHERE id = $odt_id)

                OR u.id IN (SELECT usuario_id FROM participantes WHERE odt_id = $odt_id)

            )

        ");

        $dest_comentario = [];

        $dest_wa_com = [];

        while ($ri = $res_inv->fetch_assoc()) {

            if (!empty($ri['email']))           $dest_comentario[] = ['email' => $ri['email'], 'nombre' => $ri['nombre']];

            if (!empty($ri['whatsapp_apikey'])) $dest_wa_com[]     = $ri;

        }

        if (!empty($dest_comentario)) {

            $asunto_c = "Nuevo avance en ODT #{$odt_id}: " . $tema_odt;

            $cuerpo_c = html_nuevo_comentario($mi_nombre, $tema_odt, $comentario, $odt_id);

            enviar_correo($dest_comentario, $asunto_c, $cuerpo_c);

        }

        if (!empty($dest_wa_com)) {

            $extracto = mb_strlen($comentario) > 100 ? mb_substr($comentario, 0, 100) . '...' : $comentario;

            $msg_wa_c = "💬 *Nuevo avance en ODT #{$odt_id}*\n"
                      . "*{$tema_odt}*\n"
                      . "{$mi_nombre}: {$extracto}\n"
                      . "👉 https://master.proyectbook.com/dashboard.php?view_odt={$odt_id}";

            notificar_whatsapp($dest_wa_com, $msg_wa_c);

        }

    }

    header("Location: dashboard.php?view_odt=$odt_id");

    exit;

}



// --- ACCIÓN: CERRAR ODT ---

if (isset($_POST['cerrar_odt'])) {

    $odt_id = intval($_POST['odt_id']);

    $odt_info = $conexion->query("SELECT creador_id FROM odts WHERE id = $odt_id")->fetch_assoc();

    if ($odt_info['creador_id'] == $mi_id) {

        $conexion->query("UPDATE odts SET estatus = 'cerrada' WHERE id = $odt_id");

    }

    header("Location: dashboard.php"); 

    exit;

}



// --- CONFIGURACIÓN DE ADMIN ---

if ($mi_rol == 'admin') {

    if (isset($_POST['quick_dept'])) {

        $nombre_dept = $conexion->real_escape_string($_POST['nombre_dept']);

        $conexion->query("INSERT INTO departamentos (nombre) VALUES ('$nombre_dept')");

        header("Location: dashboard.php"); 

        exit;

    }

    if (isset($_POST['delete_dept'])) {

        $conexion->query("DELETE FROM departamentos WHERE id = " . intval($_POST['dept_id']));

        header("Location: dashboard.php"); 

        exit;

    }

    if (isset($_POST['create_user'])) {

        $n = $conexion->real_escape_string($_POST['u_nombre']);

        $u = $conexion->real_escape_string($_POST['u_usuario']);

        $p = password_hash($_POST['u_pass'], PASSWORD_DEFAULT);

        $e = $conexion->real_escape_string($_POST['u_email']);

        $c = $conexion->real_escape_string($_POST['u_celular']);

        $r = $_POST['u_rol'];

        $conexion->query("INSERT INTO usuarios (nombre, usuario, password, email, celular, rol) VALUES ('$n', '$u', '$p', '$e', '$c', '$r')");

        header("Location: dashboard.php"); 

        exit;

    }

    if (isset($_POST['edit_user'])) {

        $e_id = intval($_POST['e_id']);

        $e_n = $conexion->real_escape_string($_POST['e_nombre']);

        $e_e = $conexion->real_escape_string($_POST['e_email']);

        $e_c = $conexion->real_escape_string($_POST['e_celular']);

        $e_r = $_POST['e_rol'];

        $sql_upd = "UPDATE usuarios SET nombre='$e_n', email='$e_e', celular='$e_c', rol='$e_r'";

        if (!empty($_POST['e_pass'])) {

            $sql_upd .= ", password='" . password_hash($_POST['e_pass'], PASSWORD_DEFAULT) . "'";

        }

        if (isset($_FILES['e_avatar']) && $_FILES['e_avatar']['error'] == 0) {

            $ext = pathinfo($_FILES['e_avatar']['name'], PATHINFO_EXTENSION);

            $nombre_avatar = "avatar_" . $e_id . "_" . time() . "." . $ext;

            move_uploaded_file($_FILES['e_avatar']['tmp_name'], "uploads/" . $nombre_avatar);

            $sql_upd .= ", avatar='" . $conexion->real_escape_string($nombre_avatar) . "'";

        }

        if (isset($_POST['e_wa_apikey'])) {

            $e_wa = $conexion->real_escape_string(trim($_POST['e_wa_apikey']));

            $sql_upd .= ", whatsapp_apikey = " . ($e_wa !== '' ? "'$e_wa'" : "NULL");

        }

        $conexion->query($sql_upd . " WHERE id = $e_id");

        header("Location: dashboard.php");

        exit;

    }

    if (isset($_POST['delete_user'])) {

        if (intval($_POST['del_id']) != $mi_id) { 

            $conexion->query("DELETE FROM usuarios WHERE id = " . intval($_POST['del_id'])); 

        }

        header("Location: dashboard.php"); 

        exit;

    }

}



$yo = $conexion->query("SELECT * FROM usuarios WHERE id = $mi_id")->fetch_assoc();

$tiene_avatar = !empty($yo['avatar']);

$avatar_src = $tiene_avatar ? "uploads/" . $yo['avatar'] : "";

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - ODT Proyectbook</title>

    <?php if (isset($wa_es_qr) && $wa_es_qr && isset($_GET['tab']) && $_GET['tab'] === 'whatsapp'): ?>

    <meta http-equiv="refresh" content="15;url=dashboard.php?tab=whatsapp">

    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>

        .dot { height: 12px; width: 12px; display: inline-block; border-radius: 50%; margin-right: 8px; }

        .dot-green { background-color: #28a745; }

        .dot-yellow { background-color: #ffc107; }

        .odt-urgente { border-left: 5px solid #dc3545; }

        .odt-alta { border-left: 5px solid #fd7e14; }

        .odt-media { border-left: 5px solid #0d6efd; }

        .odt-baja { border-left: 5px solid #6c757d; }

    </style>

</head>

<body class="bg-light">

<div class="container py-4">

    

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body d-flex align-items-center justify-content-between p-3">

            <div class="d-flex align-items-center">

                <div class="position-relative me-3" style="width:65px;height:65px;">

                    <?php if ($tiene_avatar): ?>

                        <img src="<?php echo $avatar_src; ?>" class="rounded-circle border" style="width:65px;height:65px;object-fit:cover;">

                    <?php else: ?>

                        <div class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center" style="width:65px;height:65px;">

                            <i class="bi bi-person-fill text-white" style="font-size:1.8rem;"></i>

                        </div>

                    <?php endif; ?>

                    <button class="btn btn-sm btn-dark position-absolute bottom-0 end-0 p-1 rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAvatar" style="width:22px;height:22px;line-height:1;">

                        <i class="bi bi-pencil-fill" style="font-size:0.6rem;"></i>

                    </button>

                </div>

                <div>

                    <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($yo['nombre']); ?></h5>

                    <small class="text-muted text-uppercase fw-semibold"><?php echo $yo['rol']; ?></small>

                </div>

            </div>

            <div>

                <?php if ($mi_rol == 'admin'): ?>

                    <button class="btn btn-outline-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAdmin">

                        <i class="bi bi-gear-fill"></i> Temas

                    </button>

                <?php endif; ?>

                <a href="logout.php" class="btn btn-danger btn-sm">

                    <i class="bi bi-box-arrow-right"></i> Salir

                </a>

            </div>

        </div>

    </div>



    <?php if (isset($_GET['view_odt'])): 

        // --- VISTA DETALLADA ---

        $odt_id = intval($_GET['view_odt']);

        $permiso_query = "SELECT o.*, u.nombre AS creador_nom, d.nombre AS dept_nom 

                          FROM odts o 

                          LEFT JOIN usuarios u ON o.creador_id = u.id 

                          JOIN departamentos d ON o.departamento_id = d.id 

                          LEFT JOIN participantes p ON o.id = p.odt_id 

                          WHERE o.id = $odt_id AND (o.creador_id = $mi_id OR p.usuario_id = $mi_id OR '$mi_rol' = 'admin') 

                          LIMIT 1";

        $res_odt = $conexion->query($permiso_query);

        

        if ($res_odt->num_rows == 0):

            echo "<div class='alert alert-danger'>No tienes permisos o la ODT no existe.</div>";

            echo "<a href='dashboard.php' class='btn btn-dark'>Volver</a>";

        else:

            $odt = $res_odt->fetch_assoc();

            $creador_nombre = $odt['creador_nom'] ?? 'Usuario Eliminado';

            

            $es_creador = ($odt['creador_id'] == $mi_id);

            $es_participante = $conexion->query("SELECT 1 FROM participantes WHERE odt_id = $odt_id AND usuario_id = $mi_id")->num_rows > 0;

            $es_involucrado = ($es_creador || $es_participante);

            ?>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>

                <?php if ($odt['estatus'] == 'abierta' && $es_creador && $mi_rol != 'admin'): ?>

                    <form method="POST" onsubmit="return confirm('¿Cerrar definitivamente esta ODT?');">

                        <input type="hidden" name="odt_id" value="<?php echo $odt_id; ?>">

                        <button type="submit" name="cerrar_odt" class="btn btn-sm btn-success">

                            <i class="bi bi-lock-fill"></i> CERRAR ODT

                        </button>

                    </form>

                <?php endif; ?>

            </div>



            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-dark text-white p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <span class="badge bg-secondary">ODT #<?php echo $odt['id']; ?></span>

                        <span class="badge bg-warning text-dark">Prioridad: <?php echo $odt['prioridad']; ?></span>

                    </div>

                    <h4 class="mt-2 mb-1 fw-bold"><?php echo htmlspecialchars($odt['tema']); ?></h4>

                    <p class="mb-3 small text-light-50">Área: <?php echo htmlspecialchars($odt['dept_nom']); ?> | Límite: <?php echo date('d/m/Y', strtotime($odt['fecha_terminacion'])); ?></p>

                    <div class="d-flex flex-wrap gap-2">

                        <?php
                        $dots = ['#60a5fa','#34d399','#f472b6','#fb923c','#a78bfa','#38bdf8','#facc15'];
                        $res_partics = $conexion->query("
                            SELECT u.id, u.nombre, u.avatar,
                                   IF(u.id = {$odt['creador_id']}, 1, 0) AS es_creador
                            FROM usuarios u
                            WHERE u.rol = 'regular'
                            AND (u.id = {$odt['creador_id']}
                               OR u.id IN (SELECT usuario_id FROM participantes WHERE odt_id = $odt_id))
                            ORDER BY es_creador DESC, u.nombre ASC
                        ");
                        $ci = 0;
                        while ($rp = $res_partics->fetch_assoc()):
                            $rp_avatar = !empty($rp['avatar']) ? "uploads/" . $rp['avatar'] : "";
                            $dot = $dots[$ci % count($dots)];
                            $ci++;
                        ?>

                            <div class="d-flex align-items-center rounded-pill px-2 py-1" style="background:rgba(255,255,255,0.1);font-size:0.78rem;">

                                <?php if ($rp_avatar): ?>

                                    <img src="<?php echo $rp_avatar; ?>" class="rounded-circle me-2" style="width:20px;height:20px;object-fit:cover;outline:2px solid <?php echo $dot; ?>;">

                                <?php else: ?>

                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:20px;height:20px;background:<?php echo $dot; ?>20;outline:2px solid <?php echo $dot; ?>;"><i class="bi bi-person-fill" style="font-size:0.65rem;color:<?php echo $dot; ?>;"></i></span>

                                <?php endif; ?>

                                <span class="text-white fw-semibold"><?php echo htmlspecialchars($rp['nombre']); ?></span>

                                <?php if ($rp['es_creador']): ?>

                                    <span class="ms-1" style="opacity:0.5;font-size:0.68rem;color:#fff;">creador</span>

                                <?php endif; ?>

                            </div>

                        <?php endwhile; ?>

                    </div>

                </div>

                <div class="card-body p-4 bg-white">

                    <div class="p-3 bg-light rounded mb-4 border-start border-dark border-3">

                        <div class="d-flex justify-content-between border-bottom pb-1 mb-2">

                            <strong><i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($creador_nombre); ?> (Creador)</strong>

                            <small class="text-muted"><?php echo $odt['fecha_creacion']; ?></small>

                        </div>

                        <p class="mb-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($odt['descripcion']); ?></p>

                        <?php if(!empty($odt['archivo'])): ?>

                            <a href="uploads/<?php echo $odt['archivo']; ?>" target="_blank" class="btn btn-sm btn-outline-dark mt-2">

                                <i class="bi bi-paperclip"></i> Adjunto original

                            </a>

                        <?php endif; ?>

                    </div>



                    <h6 class="fw-bold mb-3 text-muted"><i class="bi bi-chat-left-text-fill"></i> Historial</h6>

                    

                    <?php 

                    $evidencias = $conexion->query("SELECT e.*, u.nombre FROM evidencias e LEFT JOIN usuarios u ON e.usuario_id = u.id WHERE e.odt_id = $odt_id ORDER BY e.fecha_hora ASC");

                    while($ev = $evidencias->fetch_assoc()):

                        $ev_autor = $ev['nombre'] ?? 'Usuario Eliminado';

                    ?>

                        <div class="p-3 border rounded mb-3 bg-white shadow-sm">

                            <div class="d-flex justify-content-between border-bottom pb-1 mb-2">

                                <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($ev_autor); ?></span>

                                <small class="text-muted fw-bold"><?php echo date('d/m/Y H:i', strtotime($ev['fecha_hora'])); ?></small>

                            </div>

                            <p class="mb-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($ev['comentario']); ?></p>

                            <?php if(!empty($ev['archivo'])): ?>

                                <a href="uploads/<?php echo $ev['archivo']; ?>" target="_blank" class="btn btn-sm btn-link p-0 text-dark">

                                    <i class="bi bi-download"></i> Archivo

                                </a>

                            <?php endif; ?>

                        </div>

                    <?php endwhile; ?>



                    <?php if ($odt['estatus'] == 'abierta'): ?>

                        <?php if ($es_involucrado && $mi_rol != 'admin'): ?>

                            <div class="mt-4 pt-3 border-top">

                                <form method="POST" enctype="multipart/form-data">

                                    <input type="hidden" name="odt_id" value="<?php echo $odt_id; ?>">

                                    <div class="mb-3">

                                        <textarea name="comentario" class="form-control" rows="3" placeholder="Registrar avance..." required></textarea>

                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-4">

                                        <input type="file" name="resp_archivo" class="form-control form-control-sm w-50">

                                        <button type="submit" name="enviar_respuesta" class="btn btn-dark px-4">ENVIAR</button>

                                    </div>

                                    <?php
                                    $nuevos = $conexion->query("
                                        SELECT * FROM usuarios
                                        WHERE rol = 'regular'
                                        AND id != $mi_id
                                        AND id NOT IN (SELECT usuario_id FROM participantes WHERE odt_id = $odt_id)
                                        AND id != (SELECT creador_id FROM odts WHERE id = $odt_id)
                                        ORDER BY nombre ASC
                                    ");
                                    if ($nuevos->num_rows > 0):
                                    ?>

                                    <div class="border-top pt-3">

                                        <p class="fw-semibold small text-muted mb-2"><i class="bi bi-person-plus-fill"></i> Agregar usuarios a esta ODT</p>

                                        <div class="row">

                                        <?php while ($nu = $nuevos->fetch_assoc()):
                                            $nu_avatar_src = !empty($nu['avatar']) ? "uploads/" . $nu['avatar'] : ""; ?>

                                            <div class="col-md-4 col-sm-6 mb-2">

                                                <div class="p-2 border rounded d-flex align-items-center bg-white">

                                                    <input type="checkbox" name="nuevos_participantes[]" value="<?php echo $nu['id']; ?>" class="form-check-input me-3 ms-1">

                                                    <?php if ($nu_avatar_src): ?>

                                                        <img src="<?php echo $nu_avatar_src; ?>" class="rounded-circle border me-2 flex-shrink-0" style="width:32px;height:32px;object-fit:cover;">

                                                    <?php else: ?>

                                                        <div class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:32px;height:32px;">

                                                            <i class="bi bi-person-fill text-white" style="font-size:0.85rem;"></i>

                                                        </div>

                                                    <?php endif; ?>

                                                    <span class="small fw-semibold text-truncate"><?php echo htmlspecialchars($nu['nombre']); ?></span>

                                                </div>

                                            </div>

                                        <?php endwhile; ?>

                                        </div>

                                    </div>

                                    <?php endif; ?>

                                </form>

                            </div>

                        <?php else: ?>

                            <div class="alert alert-info text-center mb-0 mt-4 fw-bold">

                                <i class="bi bi-eye-fill"></i> MODO AUDITORÍA: Eres administrador, pero no participas en esta ODT. Solo lectura.

                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <div class="alert alert-secondary text-center mb-0 mt-4 fw-bold">ODT CERRADA</div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>



    <?php else: 

        // --- PESTAÑAS PRINCIPALES ---

    ?>

        <nav class="mb-3 border-bottom pb-2">

            <div class="nav nav-pills" id="nav-tab" role="tablist">

                <button class="nav-link active fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-abiertas" type="button"><i class="bi bi-clipboard2-pulse-fill me-1"></i> ODTs ABIERTAS</button>

                <button class="nav-link fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-cerradas" type="button"><i class="bi bi-archive-fill me-1"></i> ODTs CERRADAS</button>

                <?php if ($mi_rol != 'admin'): ?>

                <button class="nav-link fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-nueva" type="button">

                    <i class="bi bi-plus-circle-fill"></i> NUEVA ODT

                </button>

                <?php endif; ?>

                <?php if ($mi_rol == 'admin'): ?>

                    <button class="nav-link fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-whatsapp" type="button">

                        <i class="bi bi-whatsapp"></i> WHATSAPP

                    </button>

                    <button class="nav-link fw-bold text-dark ms-auto" data-bs-toggle="tab" data-bs-target="#tab-usuarios" type="button">

                        <i class="bi bi-people-fill"></i> USUARIOS

                    </button>

                <?php endif; ?>

            </div>

        </nav>



        <div class="tab-content" id="nav-tabContent">

            <div class="tab-pane fade show active" id="tab-abiertas">

                <div class="list-group">

                <?php 

                $hoy = date('Y-m-d');

                $q_abiertas = "SELECT DISTINCT o.*, u.nombre AS creador_nom, 

                               (SELECT usuario_id FROM evidencias WHERE odt_id = o.id ORDER BY fecha_hora DESC LIMIT 1) AS ult_msg_id, 

                               (SELECT COUNT(*) FROM evidencias WHERE odt_id = o.id) AS total_msg 

                               FROM odts o 

                               LEFT JOIN usuarios u ON o.creador_id = u.id 

                               LEFT JOIN participantes p ON o.id = p.odt_id 

                               WHERE o.estatus = 'abierta' AND (o.creador_id = $mi_id OR p.usuario_id = $mi_id OR '$mi_rol' = 'admin') 

                               ORDER BY o.fecha_creacion DESC";

                $res_abiertas = $conexion->query($q_abiertas);

                

                if($res_abiertas->num_rows == 0) echo "<p class='text-muted p-3 text-center'>No hay ODTs abiertas.</p>";

                

                while($odt = $res_abiertas->fetch_assoc()):

                    $color_texto = ($hoy > $odt['fecha_terminacion']) ? "text-danger" : "text-dark";

                    $dot_class = ($odt['total_msg'] == 0 && $odt['creador_id'] == $mi_id) ? "dot dot-green" : (($odt['total_msg'] > 0 && $odt['ult_msg_id'] != $mi_id) ? "dot dot-yellow" : "");

                    $prio_class = "odt-" . strtolower($odt['prioridad']);

                    $creador = $odt['creador_nom'] ?? 'Usuario Eliminado';

                ?>

                    <a href="dashboard.php?view_odt=<?php echo $odt['id']; ?>" class="list-group-item list-group-item-action mb-2 shadow-sm rounded border-0 <?php echo $prio_class; ?> p-3">

                        <div class="d-flex w-100 justify-content-between align-items-center">

                            <h6 class="mb-1 fw-bold <?php echo $color_texto; ?>">

                                <?php if(!empty($dot_class)) echo "<span class='$dot_class'></span>"; ?> 

                                ODT #<?php echo $odt['id']; ?> - <?php echo htmlspecialchars($odt['tema']); ?>

                            </h6>

                            <small class="text-muted fw-bold">Vence: <?php echo date('d/m/Y', strtotime($odt['fecha_terminacion'])); ?></small>

                        </div>

                        <div class="d-flex justify-content-between mt-2">

                            <small class="text-muted">Por: <?php echo htmlspecialchars($creador); ?></small>

                            <span class="badge bg-light text-dark border py-1"><?php echo $odt['prioridad']; ?></span>

                        </div>

                    </a>

                <?php endwhile; ?>

                </div>

            </div>



            <div class="tab-pane fade" id="tab-cerradas">

                <div class="list-group">

                <?php 

                $q_cerradas = "SELECT DISTINCT o.*, u.nombre AS creador_nom 

                               FROM odts o 

                               LEFT JOIN usuarios u ON o.creador_id = u.id 

                               LEFT JOIN participantes p ON o.id = p.odt_id 

                               WHERE o.estatus = 'cerrada' AND (o.creador_id = $mi_id OR p.usuario_id = $mi_id OR '$mi_rol' = 'admin') 

                               ORDER BY o.fecha_creacion DESC";

                $res_cerradas = $conexion->query($q_cerradas);

                

                if($res_cerradas->num_rows == 0) echo "<p class='text-muted p-3 text-center'>No hay histórico.</p>";

                

                while($odt = $res_cerradas->fetch_assoc()):

                ?>

                    <a href="dashboard.php?view_odt=<?php echo $odt['id']; ?>" class="list-group-item list-group-item-action mb-2 shadow-sm rounded border-0 p-3 opacity-75">

                        <h6 class="mb-1 fw-bold text-secondary">

                            <i class="bi bi-check-circle-fill text-success"></i> 

                            ODT #<?php echo $odt['id']; ?> - <?php echo htmlspecialchars($odt['tema']); ?>

                        </h6>

                    </a>

                <?php endwhile; ?>

                </div>

            </div>



            <div class="tab-pane fade" id="tab-nueva">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <form method="POST" enctype="multipart/form-data">

                            <div class="mb-3">

                                <label class="form-label fw-semibold">Tema</label>

                                <input type="text" name="tema" class="form-control" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label fw-semibold">Descripción</label>

                                <textarea name="descripcion" class="form-control" rows="4" required></textarea>

                            </div>

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">Departamento</label>

                                    <select name="departamento_id" class="form-select" required>

                                        <option value="">Selecciona...</option>

                                        <?php 

                                        $depts = $conexion->query("SELECT * FROM departamentos ORDER BY nombre ASC"); 

                                        while($d = $depts->fetch_assoc()) {

                                            echo "<option value='".$d['id']."'>".htmlspecialchars($d['nombre'])."</option>"; 

                                        }

                                        ?>

                                    </select>

                                </div>

                                <div class="col-md-3 mb-3">

                                    <label class="form-label fw-semibold">Prioridad</label>

                                    <select name="prioridad" class="form-select" required>

                                        <option value="Baja">Baja</option>

                                        <option value="Media" selected>Media</option>

                                        <option value="Alta">Alta</option>

                                        <option value="Urgente">Urgente</option>

                                    </select>

                                </div>

                                <div class="col-md-3 mb-3">

                                    <label class="form-label fw-semibold">Vencimiento</label>

                                    <input type="date" name="fecha_terminacion" class="form-control" required min="<?php echo date('Y-m-d'); ?>">

                                </div>

                            </div>

                            <div class="mb-4">

                                <label class="form-label fw-semibold">Evidencia (Foto/Doc)</label>

                                <input type="file" name="odt_archivo" class="form-control">

                            </div>

                            <div class="mb-4">

                                <label class="form-label fw-semibold d-block mb-3">Participantes</label>

                                <div class="row">

                                <?php 

                                $staff = $conexion->query("SELECT * FROM usuarios WHERE id != $mi_id AND rol = 'regular' ORDER BY nombre ASC");

                                while($st = $staff->fetch_assoc()): 

                                    $st_tiene_avatar = !empty($st['avatar']);

                                    $st_avatar = $st_tiene_avatar ? "uploads/" . $st['avatar'] : "";

                                ?>

                                    <div class="col-md-4 col-sm-6 mb-3">

                                        <div class="p-2 border rounded d-flex align-items-center bg-white">

                                            <input type="checkbox" name="participantes[]" value="<?php echo $st['id']; ?>" class="form-check-input me-3 ms-1">

                                            <?php if ($st_tiene_avatar): ?>

                                                <img src="<?php echo $st_avatar; ?>" class="rounded-circle border me-2" style="width:35px;height:35px;object-fit:cover;">

                                            <?php else: ?>

                                                <div class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:35px;height:35px;">

                                                    <i class="bi bi-person-fill text-white" style="font-size:1rem;"></i>

                                                </div>

                                            <?php endif; ?>

                                            <span class="small fw-semibold text-truncate"><?php echo htmlspecialchars($st['nombre']); ?></span>

                                        </div>

                                    </div>

                                <?php endwhile; ?>

                                </div>

                            </div>

                            <button type="submit" name="crear_odt" class="btn btn-dark w-100 py-2 fw-bold">CREAR ORDEN</button>

                        </form>

                    </div>

                </div>

            </div>



            <?php if ($mi_rol == 'admin'): ?>

            <div class="tab-pane fade" id="tab-usuarios">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">

                        <h5 class="mb-0 fw-bold">Personal</h5>

                        <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">

                            <i class="bi bi-person-plus-fill"></i> Agregar

                        </button>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light text-muted small">

                                    <tr>

                                        <th class="ps-4">Usuario</th>

                                        <th>Contacto</th>

                                        <th>Rol</th>

                                        <th class="text-end pe-4">Acciones</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php 

                                    $usrs = $conexion->query("SELECT * FROM usuarios ORDER BY nombre ASC"); 

                                    while($u = $usrs->fetch_assoc()): 

                                        $u_tiene_avatar = !empty($u['avatar']);

                                        $u_avatar = $u_tiene_avatar ? "uploads/" . $u['avatar'] : "";

                                    ?>

                                    <tr>

                                        <td class="ps-4">

                                            <div class="d-flex align-items-center">

                                                <?php if ($u_tiene_avatar): ?>

                                                    <img src="<?php echo $u_avatar; ?>" class="rounded-circle border me-3" style="width:40px;height:40px;object-fit:cover;">

                                                <?php else: ?>

                                                    <div class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:40px;height:40px;">

                                                        <i class="bi bi-person-fill text-white" style="font-size:1.1rem;"></i>

                                                    </div>

                                                <?php endif; ?>

                                                <div>

                                                    <div class="fw-bold"><?php echo htmlspecialchars($u['nombre']); ?></div>

                                                    <div class="small text-muted">@<?php echo htmlspecialchars($u['usuario']); ?></div>

                                                </div>

                                            </div>

                                        </td>

                                        <td>

                                            <div class="small"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($u['email']); ?></div>

                                            <div class="small"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($u['celular'] ?? 'N/A'); ?></div>

                                        </td>

                                        <td>

                                            <span class="badge bg-<?php echo ($u['rol']=='admin')?'dark':'secondary'; ?>">

                                                <?php echo strtoupper($u['rol']); ?>

                                            </span>

                                        </td>

                                        <td class="text-end pe-4">

                                            <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#modalEditUser<?php echo $u['id']; ?>">

                                                <i class="bi bi-pencil-fill"></i>

                                            </button>

                                            <?php if($u['id']!=$mi_id): ?>

                                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Borrar a este usuario?');">

                                                    <input type="hidden" name="del_id" value="<?php echo $u['id']; ?>">

                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">

                                                        <i class="bi bi-x-lg"></i>

                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                    

                                    <div class="modal fade" id="modalEditUser<?php echo $u['id']; ?>" tabindex="-1">

                                        <div class="modal-dialog">

                                            <div class="modal-content bg-white shadow">

                                                <form method="POST" enctype="multipart/form-data" class="text-start">

                                                    <div class="modal-header bg-light">

                                                        <h5 class="modal-title fw-bold">Editar Personal</h5>

                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                                                    </div>

                                                    <div class="modal-body">

                                                        <input type="hidden" name="e_id" value="<?php echo $u['id']; ?>">

                                                        <div class="mb-3">

                                                            <label class="form-label small fw-bold">Nombre Completo</label>

                                                            <input type="text" name="e_nombre" class="form-control" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>

                                                        </div>

                                                        <div class="row">

                                                            <div class="col-6 mb-3">

                                                                <label class="form-label small fw-bold">Email</label>

                                                                <input type="email" name="e_email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>

                                                            </div>

                                                            <div class="col-6 mb-3">

                                                                <label class="form-label small fw-bold">Celular</label>

                                                                <input type="text" name="e_celular" class="form-control" value="<?php echo htmlspecialchars($u['celular']); ?>">

                                                            </div>

                                                        </div>

                                                        <div class="mb-3">

                                                            <label class="form-label small fw-bold">Rol</label>

                                                            <select name="e_rol" class="form-select" required>

                                                                <option value="regular" <?php if($u['rol']=='regular') echo 'selected'; ?>>Regular</option>

                                                                <option value="admin" <?php if($u['rol']=='admin') echo 'selected'; ?>>Administrador</option>

                                                            </select>

                                                        </div>

                                                        <div class="mb-3">

                                                            <label class="form-label small fw-bold">Foto de Perfil</label>

                                                            <div class="d-flex align-items-center gap-3">

                                                                <?php if ($u_tiene_avatar): ?>

                                                                    <img src="<?php echo $u_avatar; ?>" id="preview_<?php echo $u['id']; ?>" class="rounded-circle border flex-shrink-0" style="width:56px;height:56px;object-fit:cover;">

                                                                <?php else: ?>

                                                                    <div id="preview_placeholder_<?php echo $u['id']; ?>" class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">

                                                                        <i class="bi bi-person-fill text-white" style="font-size:1.5rem;"></i>

                                                                    </div>

                                                                    <img src="" id="preview_<?php echo $u['id']; ?>" class="rounded-circle border flex-shrink-0 d-none" style="width:56px;height:56px;object-fit:cover;">

                                                                <?php endif; ?>

                                                                <input type="file" name="e_avatar" class="form-control form-control-sm" accept="image/*"

                                                                    onchange="
                                                                        var ph = document.getElementById('preview_placeholder_<?php echo $u['id']; ?>');
                                                                        if(ph) ph.classList.add('d-none');
                                                                        var img = document.getElementById('preview_<?php echo $u['id']; ?>');
                                                                        img.src = URL.createObjectURL(this.files[0]);
                                                                        img.classList.remove('d-none');
                                                                    ">

                                                            </div>

                                                        </div>

                                                        <div class="mb-3">

                                                            <label class="form-label small fw-bold"><i class="bi bi-whatsapp text-success"></i> API Key WhatsApp <span class="text-muted fw-normal">(CallMeBot)</span></label>

                                                            <input type="text" name="e_wa_apikey" class="form-control form-control-sm" placeholder="Dejar vacío para borrar" value="<?php echo htmlspecialchars($u['whatsapp_apikey'] ?? ''); ?>">

                                                        </div>

                                                        <div class="mb-2 p-3 bg-light rounded border border-danger-subtle">

                                                            <label class="form-label small fw-bold text-danger">

                                                                <i class="bi bi-key-fill"></i> Forzar Nueva Contraseña

                                                            </label>

                                                            <input type="password" name="e_pass" class="form-control form-control-sm" placeholder="Solo si deseas cambiarla">

                                                        </div>

                                                    </div>

                                                    <div class="modal-footer border-top-0">

                                                        <button type="submit" name="edit_user" class="btn btn-dark w-100">Guardar Cambios</button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>



            <div class="modal fade" id="modalNuevoUsuario" tabindex="-1">

                <div class="modal-dialog">

                    <div class="modal-content bg-white shadow">

                        <form method="POST">

                            <div class="modal-header bg-dark text-white">

                                <h5 class="modal-title fw-bold">Registrar Personal</h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <div class="mb-3">

                                    <label class="form-label small fw-bold">Nombre</label>

                                    <input type="text" name="u_nombre" class="form-control" required>

                                </div>

                                <div class="row">

                                    <div class="col-6 mb-3">

                                        <label class="form-label small fw-bold">Usuario</label>

                                        <input type="text" name="u_usuario" class="form-control" required>

                                    </div>

                                    <div class="col-6 mb-3">

                                        <label class="form-label small fw-bold">Contraseña</label>

                                        <input type="text" name="u_pass" class="form-control" required>

                                    </div>

                                    <div class="col-6 mb-3">

                                        <label class="form-label small fw-bold">Email</label>

                                        <input type="email" name="u_email" class="form-control" required>

                                    </div>

                                    <div class="col-6 mb-3">

                                        <label class="form-label small fw-bold">Celular</label>

                                        <input type="text" name="u_celular" class="form-control">

                                    </div>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label small fw-bold">Rol</label>

                                    <select name="u_rol" class="form-select" required>

                                        <option value="regular" selected>Regular</option>

                                        <option value="admin">Administrador</option>

                                    </select>

                                </div>

                            </div>

                            <div class="modal-footer">

                                <button type="submit" name="create_user" class="btn btn-dark w-100">Crear Cuenta</button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <?php endif; ?>



            <?php if ($mi_rol == 'admin'): ?>

            <div class="tab-pane fade" id="tab-whatsapp">

                <?php

                // Consultar estado del servidor WPPConnect

                $wa_api_url  = 'http://127.0.0.1:3030';

                $wa_api_key  = 'odt-proyectbook-2026';

                $wa_ctx      = stream_context_create(['http' => [

                    'header'        => "x-api-key: {$wa_api_key}\r\n",

                    'timeout'       => 3,

                    'ignore_errors' => true,

                ]]);

                $wa_resp  = @file_get_contents("{$wa_api_url}/status", false, $wa_ctx);

                $wa_data  = $wa_resp ? json_decode($wa_resp, true) : null;

                $wa_estado = $wa_data['estado'] ?? 'offline';

                $wa_es_qr  = str_starts_with($wa_estado, 'qr:');

                $wa_qr_img = $wa_es_qr ? str_replace('qr:', '', $wa_estado) : '';

                $wa_ok     = ($wa_estado === 'conectado');

                $wa_offline = ($wa_resp === false);

                ?>

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-dark text-white p-3 d-flex align-items-center justify-content-between">

                        <h5 class="mb-0 fw-bold"><i class="bi bi-whatsapp me-2"></i>Conexión WhatsApp</h5>

                        <span class="badge fs-6 <?php echo $wa_ok ? 'bg-success' : ($wa_es_qr ? 'bg-warning text-dark' : ($wa_offline ? 'bg-danger' : 'bg-secondary')); ?>">

                            <?php echo $wa_ok ? '✅ Conectado' : ($wa_es_qr ? '📱 Escanea el QR' : ($wa_offline ? '🔴 Servidor offline' : '⏳ Iniciando...')); ?>

                        </span>

                    </div>

                    <div class="card-body p-4 text-center">

                    <?php if ($wa_offline): ?>

                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:3rem;"></i>

                        <p class="mt-3 text-danger fw-bold">El servidor WPPConnect no está corriendo.</p>

                        <p class="text-muted small">Desde el servidor ejecuta:<br><code>/home/claudeproyectbook/wppconnect/node_modules/.bin/pm2 start /home/claudeproyectbook/wppconnect/server.js --name wppconnect-odt</code></p>

                    <?php elseif ($wa_ok): ?>

                        <i class="bi bi-whatsapp text-success" style="font-size:4rem;"></i>

                        <p class="mt-3 fw-bold fs-5 text-success">WhatsApp conectado y operando</p>

                        <p class="text-muted small mb-4">El sistema enviará alertas automáticamente a los participantes de cada ODT.</p>

                        <form method="POST" onsubmit="return confirm('¿Desconectar WhatsApp? Deberás escanear un QR nuevo para reconectar.');">

                            <button type="submit" name="wa_disconnect" class="btn btn-outline-danger px-4">

                                <i class="bi bi-plug-fill"></i> Desconectar

                            </button>

                        </form>

                    <?php elseif ($wa_es_qr): ?>

                        <p class="text-muted mb-3">Abre WhatsApp en tu celular → <strong>Dispositivos vinculados</strong> → <strong>Vincular dispositivo</strong> → Escanea:</p>

                        <img src="<?php echo $wa_qr_img; ?>" class="rounded-3 border" style="width:260px;height:260px;" alt="QR WhatsApp">

                        <p class="text-muted small mt-3">Esta página se recarga automáticamente cada 15 segundos.</p>

                        <form method="POST" class="mt-2">

                            <button type="submit" name="wa_new_qr" class="btn btn-outline-secondary btn-sm">

                                <i class="bi bi-arrow-clockwise"></i> Generar nuevo QR

                            </button>

                        </form>

                    <?php else: ?>

                        <div class="spinner-border text-success mb-3" role="status"></div>

                        <p class="text-muted">Iniciando conexión, espera unos segundos...</p>

                    <?php endif; ?>

                    </div>

                </div>

            </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<?php if (isset($_POST['wa_disconnect'])): ?>

    <?php

    $ctx_wa = stream_context_create(['http' => [

        'method'        => 'POST',

        'header'        => "x-api-key: odt-proyectbook-2026\r\nContent-Length: 0\r\n",

        'timeout'       => 5,

        'ignore_errors' => true,

    ]]);

    @file_get_contents('http://127.0.0.1:3030/disconnect', false, $ctx_wa);

    header("Location: dashboard.php#tab-whatsapp");

    exit;

    ?>

<?php endif; ?>

<?php if (isset($_POST['wa_new_qr'])): ?>

    <?php

    $ctx_wa2 = stream_context_create(['http' => [

        'method'        => 'POST',

        'header'        => "x-api-key: odt-proyectbook-2026\r\nContent-Length: 0\r\n",

        'timeout'       => 5,

        'ignore_errors' => true,

    ]]);

    @file_get_contents('http://127.0.0.1:3030/new-qr', false, $ctx_wa2);

    sleep(3);

    header("Location: dashboard.php#tab-whatsapp");

    exit;

    ?>

<?php endif; ?>



<div class="modal fade" id="modalAvatar" tabindex="-1">

    <div class="modal-dialog">

        <form method="POST" enctype="multipart/form-data" class="modal-content">

            <div class="modal-header bg-dark text-white">

                <h5 class="modal-title fw-bold">Mi Perfil</h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label class="form-label small fw-bold">Foto de perfil</label>

                    <input type="file" name="avatar_file" class="form-control" accept="image/*">

                </div>

                <div class="border-top pt-3">

                    <label class="form-label small fw-bold">API Key de WhatsApp <span class="text-muted fw-normal">(CallMeBot)</span></label>

                    <input type="text" name="wa_apikey" class="form-control" placeholder="La key que te envió CallMeBot por WhatsApp"
                        value="<?php echo htmlspecialchars($yo['whatsapp_apikey'] ?? ''); ?>">

                    <div class="form-text">Agrega el contacto <strong>+34 644 59 21 64</strong> a WhatsApp y envíale: <code>I allow callmebot to send me messages</code>. Te responderá con tu API key.</div>

                </div>

            </div>

            <div class="modal-footer">

                <button type="submit" name="update_avatar" class="btn btn-dark w-100">Guardar</button>

            </div>

        </form>

    </div>

</div>



<?php if ($mi_rol == 'admin'): ?>

<div class="modal fade" id="modalAdmin" tabindex="-1">

    <div class="modal-dialog modal-sm">

        <div class="modal-content">

            <div class="modal-header bg-dark text-white">

                <h5 class="modal-title fw-bold">Departamentos</h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body p-3">

                <form method="POST" class="mb-3">

                    <div class="input-group">

                        <input type="text" name="nombre_dept" class="form-control form-control-sm" required>

                        <button type="submit" name="quick_dept" class="btn btn-sm btn-dark">Agregar</button>

                    </div>

                </form>

                <ul class="list-group list-group-flush border rounded overflow-y-auto" style="max-height:250px;">

                    <?php 

                    $d_list = $conexion->query("SELECT * FROM departamentos ORDER BY nombre ASC"); 

                    while($dl = $d_list->fetch_assoc()): 

                    ?>

                        <li class='list-group-item small py-1 px-2 d-flex justify-content-between align-items-center'>

                            <?php echo htmlspecialchars($dl['nombre']); ?>

                            <form method='POST' style='margin:0;' onsubmit="return confirm('¿Borrar departamento?');">

                                <input type='hidden' name='dept_id' value='<?php echo $dl['id']; ?>'>

                                <button type='submit' name='delete_dept' class='btn p-0 border-0 bg-transparent text-danger'>

                                    <i class='bi bi-x-lg'></i>

                                </button>

                            </form>

                        </li>

                    <?php endwhile; ?>

                </ul>

            </div>

        </div>

    </div>

</div>

<?php endif; ?>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    // Activar pestaña desde URL ?tab=xxx

    const tabParam = new URLSearchParams(window.location.search).get('tab');

    if (tabParam) {

        const tabEl = document.querySelector('[data-bs-target="#tab-' + tabParam + '"]');

        if (tabEl) new bootstrap.Tab(tabEl).show();

    }

</script>

</body>

</html>