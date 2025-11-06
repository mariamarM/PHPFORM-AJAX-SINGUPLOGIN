<!-- <?php
session_start();
$_SESSION['usuario'] = $usuario;
$_SESSION['rol'] = $rol;
$_SESSION['empresa'] = $empresa;

if ($_SESSION['rol'] === 'admin') {
    header('Location: index_ajax.html');
} else {
    header('Location: index.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body class="bodylog">
    <div class="scroll-container">
        <h1 class="titulo-scroll">Login Sociometric Survey Form</h1>
    </div>
    <form action="./index.php" method="post" class="formlogbox">
    <input type="text" name="username" placeholder="Username" class="formlog">
    <select name="rol" class="formlog">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    <input type="password" name="password" placeholder="password" class="formlog">
        <input type="submit" name="IniciarSesion" value="Log in" class="formlog">

    </form>
        <input type="button" name="CerrarSesion" value="Cerrar Sesion" onclick="location.href='logout.php'" class="formlogCerrar">

    <div class="formpartesRespuestas">
    <div class="verOtrosLogin">
        <p>If you don't have an account you can sign in.</p>
        <button id="verRespuestas" onclick="window.location.href='./auth/singup.php'">Sign up</button>
    </div>
</div>
</body>

</html> -->