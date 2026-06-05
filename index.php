<?php
session_start();
require 'conexion.php';

// Si el usuario ya está logueado, lo mandamos directo al tablero
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $conexion->real_escape_string($_POST['usuario']);
    $password = $_POST['password'];

    $resultado = $conexion->query("SELECT * FROM usuarios WHERE usuario = '$usuario'");
    
    if ($resultado->num_rows > 0) {
        $user = $resultado->fetch_assoc();
        // Verificamos la contraseña encriptada
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['nombre'] = $user['nombre'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El usuario no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso - Sistema ODT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 col-sm-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4 fw-bold">ODT Proyectbook</h3>
                        
                        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted">Usuario</label>
                                <input type="text" name="usuario" class="form-control" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-2">Entrar al Sistema</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>