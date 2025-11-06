<!-- <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $nombreUsuario = $_POST['Username'] ?? '';
    $email = $_POST['Email'] ?? '';
    $empresa = $_POST['Organization'] ?? '';
    $rol = $_POST['Rol'] ?? '';

    if (empty($rol)) {
        $rol = 'user';
    }

    $_SESSION['usuario'] = $nombreUsuario;
    $_SESSION['rol'] = $rol;
    $_SESSION['empresa'] = $empresa;
    $_SESSION['email'] = $email;

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

            <form action="/login.php" method="post" class="formlogbox">
                <input type="text" name="Username" required="" placeholder="Username">

                <input type="email" name="Email" required="" placeholder="Email">

                <input type="text" name="Organization" required="" placeholder="Organization">

                <input type="password" name="Password" required="" placeholder="Password">


                <select name="Rol" id="rol" required>
                    <option value="" disabled selected >Select role</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <input type="submit" name="Send" value="Send" <? if ($rol === 'admin') {
    header('Location: ../index_ajax.html');
} else {
    header('Location: ../index.php');
} ?>>
            </form>
        </div>
</body>

</html> -->