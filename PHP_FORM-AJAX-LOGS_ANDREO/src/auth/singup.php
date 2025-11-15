<?php
session_start();

$file = __DIR__ . "/../storage/data.json";

$usuarios = json_decode(file_get_contents($file), true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreUsuario = $_POST['Username'] ?? '';
    $email         = $_POST['Email'] ?? '';
    $empresa       = $_POST['Organization'] ?? '';
    $password      = $_POST['Password'] ?? '';
    $rol           = $_POST['Rol'] ?? 'user'; // defecto

    if (!$nombreUsuario || !$email || !$password) {
        die("Faltan campos obligatorios");
    }

    $nuevoId = !empty($usuarios) ? end($usuarios)["id"] + 1 : 1;

    $passHash = password_hash($password, PASSWORD_DEFAULT);

    $nuevoUsuario = [
        "id"      => $nuevoId,
        "nombre"  => $nombreUsuario,
        "email"   => $email,
        "empresa" => $empresa,
        "rol"     => $rol,
        "password"=> $passHash
    ];
  $usuarios[] = $nuevoUsuario;
    file_put_contents($file, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION['id']      = $nuevoId;
    $_SESSION['usuario'] = $nombreUsuario;
    $_SESSION['rol']     = $rol;
    $_SESSION['empresa'] = $empresa;
    $_SESSION['email']   = $email;

  
    if ($rol === 'admin') {
        header("Location: ../index_ajax.html");
    } else {
        header("Location: ./login.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
    <div class="bodylog">
        <div class="scroll-container">
            <h1 class="titulo-scroll">Sign up Sociometric Survey Form</h1>
        </div>
        <div class="signup-box">

            <form action="singup.php" method="post" class="formlogbox">
                <input type="text" name="Username" required placeholder="Username">
                <input type="email" name="Email" required placeholder="Email">
                <input type="text" name="Organization" required placeholder="Organization">
                <input type="password" name="Password" required placeholder="Password">

                <select name="Rol" id="rol" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <input type="submit" name="Send" value="Send">
            </form>

        </div>
    </div>
</body>
</html>
