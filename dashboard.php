<?php

// Rastreador de errores para aislar problemas

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



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

    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {

        $ext = pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION);

        $nombre_archivo = "avatar_" . $mi_id . "_" . time() . "." . $ext;

        if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], "uploads/" . $nombre_archivo)) {

            $conexion->query("UPDATE usuarios SET avatar = '$nombre_archivo' WHERE id = $mi_id");

            header("Location: dashboard.php"); 

            exit;

        }

    }

}



// --- ACCIÓN: CREAR NUEVA ODT ---

if (isset($_POST['crear_odt'])) {

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

        if (isset($_POST['participantes']) && is_array($_POST['participantes'])) {

            foreach ($_POST['participantes'] as $p_id) {

                $conexion->query("INSERT INTO participantes (odt_id, usuario_id) VALUES ($odt_id, " . intval($p_id) . ")");

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

    

    if ($odt_info['estatus'] == 'abierta' && ($soy_creador || $soy_partic || $mi_rol == 'admin')) {

        $archivo_db = "";

        if (isset($_FILES['resp_archivo']) && $_FILES['resp_archivo']['error'] == 0) {

            $archivo_db = "evid_" . time() . "_" . basename($_FILES['resp_archivo']['name']);

            move_uploaded_file($_FILES['resp_archivo']['tmp_name'], "uploads/" . $archivo_db);

        }

        $conexion->query("INSERT INTO evidencias (odt_id, usuario_id, comentario, archivo) VALUES ($odt_id, $mi_id, '$comentario', '$archivo_db')");

    }

    header("Location: dashboard.php?view_odt=$odt_id"); 

    exit;

}



// --- ACCIÓN: CERRAR ODT ---

if (isset($_POST['cerrar_odt'])) {

    $odt_id = intval($_POST['odt_id']);

    $odt_info = $conexion->query("SELECT creador_id FROM odts WHERE id = $odt_id")->fetch_assoc();

    if ($odt_info['creador_id'] == $mi_id || $mi_rol == 'admin') { 

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

$avatar_src = (!empty($yo['avatar'])) ? "uploads/" . $yo['avatar'] : "https://via.placeholder.com/80";

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - ODT Proyectbook</title>

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

                <div class="position-relative me-3">

                    <img src="<?php echo $avatar_src; ?>" class="rounded-circle border" style="width: 65px; height: 65px; object-fit: cover;">

                    <button class="btn btn-sm btn-dark position-absolute bottom-0 end-0 p-1 rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAvatar">

                        <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>

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

                <?php if ($odt['estatus'] == 'abierta' && $es_creador): ?>

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

                    <h4 class="mt-2 mb-0 fw-bold"><?php echo htmlspecialchars($odt['tema']); ?></h4>

                    <p class="mb-0 small text-light-50">Área: <?php echo htmlspecialchars($odt['dept_nom']); ?> | Límite: <?php echo $odt['fecha_terminacion']; ?></p>

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

                        <?php if ($es_involucrado || $mi_rol == 'admin'): ?>

                            <div class="mt-4 pt-3 border-top">

                                <form method="POST" enctype="multipart/form-data">

                                    <input type="hidden" name="odt_id" value="<?php echo $odt_id; ?>">

                                    <div class="mb-3">

                                        <textarea name="comentario" class="form-control" rows="3" placeholder="Registrar avance..." required></textarea>

                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">

                                        <input type="file" name="resp_archivo" class="form-control form-control-sm w-50">

                                        <button type="submit" name="enviar_respuesta" class="btn btn-dark px-4">ENVIAR</button>

                                    </div>

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

                <button class="nav-link active fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-abiertas" type="button">ODTs ABIERTAS</button>

                <button class="nav-link fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-cerradas" type="button">ODTs CERRADAS</button>

                <button class="nav-link fw-bold text-dark me-2" data-bs-toggle="tab" data-bs-target="#tab-nueva" type="button">

                    <i class="bi bi-plus-circle-fill"></i> NUEVA ODT

                </button>

                <?php if ($mi_rol == 'admin'): ?>

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

                                $staff = $conexion->query("SELECT * FROM usuarios WHERE id != $mi_id ORDER BY nombre ASC"); 

                                while($st = $staff->fetch_assoc()): 

                                    $st_avatar = (!empty($st['avatar'])) ? "uploads/" . $st['avatar'] : "https://via.placeholder.com/40"; 

                                ?>

                                    <div class="col-md-4 col-sm-6 mb-3">

                                        <div class="p-2 border rounded d-flex align-items-center bg-white">

                                            <input type="checkbox" name="participantes[]" value="<?php echo $st['id']; ?>" class="form-check-input me-3 ms-1">

                                            <img src="<?php echo $st_avatar; ?>" class="rounded-circle border me-2" style="width:35px;height:35px;object-fit:cover;">

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

                                        $u_avatar = (!empty($u['avatar'])) ? "uploads/" . $u['avatar'] : "https://via.placeholder.com/40"; 

                                    ?>

                                    <tr>

                                        <td class="ps-4">

                                            <div class="d-flex align-items-center">

                                                <img src="<?php echo $u_avatar; ?>" class="rounded-circle border me-3" style="width:40px;height:40px;object-fit:cover;">

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

                                                <form method="POST" class="text-start">

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

                                                        <div class="mb-2 p-3 bg-light rounded border border-danger-subtle">

                                                            <label class="form-label small fw-bold text-danger">

                                                                <i class="bi bi-key-fill"></i> Forzar Nueva Contraseña

                                                            </label>

                                                            <input type="text" name="e_pass" class="form-control form-control-sm" placeholder="Solo si deseas cambiarla">

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

        </div>

    <?php endif; ?>

</div>



<div class="modal fade" id="modalAvatar" tabindex="-1">

    <div class="modal-dialog modal-sm">

        <form method="POST" enctype="multipart/form-data" class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">Foto</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <input type="file" name="avatar_file" class="form-control" required accept="image/*">

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

</body>

</html>